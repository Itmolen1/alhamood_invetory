<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\ISupplierAdvanceRepositoryInterface;
use App\Http\Requests\SupplierAdvanceRequest;
use App\Http\Resources\SupplierAdvance\SupplierAdvanceResource;
use App\Models\AccountTransaction;
use App\Models\Bank;
use App\Models\PaymentType;
use App\Models\Supplier;
use App\Models\SupplierAdvance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SupplierAdvanceRepository implements ISupplierAdvanceRepositoryInterface
{
    public function all()
    {
        return SupplierAdvanceResource::collection(SupplierAdvance::all()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return SupplierAdvanceResource::Collection(SupplierAdvance::with('api_supplier')->get()->sortDesc()->forPage($page_no,$page_size));
    }

    public function BaseList()
    {
        return array('supplier'=>Supplier::select('id','Name')->orderBy('id','desc')->get(),'payment_type'=>PaymentType::select('id','Name')->orderBy('id','desc')->get(),'bank'=>Bank::select('id','Name')->orderBy('id','desc')->get());
    }

    public function insert(Request $request)
    {
        $userId = Auth::id();
        $supplier_advance = new SupplierAdvance();
        $supplier_advance->supplier_id=$request->supplier_id;
        $supplier_advance->receiptNumber=$request->receiptNumber;
        $supplier_advance->paymentType=$request->paymentType;
        $supplier_advance->Amount=$request->Amount;
        $supplier_advance->sumOf=Str::getUAECurrency($request->Amount);
        $supplier_advance->receiverName=$request->receiverName;
        $supplier_advance->Description=$request->Description;
        $supplier_advance->user_id=$request->user_id;
        $supplier_advance->bank_id=$request->bank_id;
        $supplier_advance->accountNumber=$request->accountNumber;
        $supplier_advance->TransferDate=$request->TransferDate;
        $supplier_advance->registerDate=$request->registerDate;
        $supplier_advance->createdDate=date('Y-m-d h:i:s');
        $supplier_advance->isActive=1;
        $supplier_advance->isActive=1;
        $supplier_advance->user_id = $userId ?? 0;
        $supplier_advance->company_id=Str::getCompany($userId);
        $supplier_advance->save();
        return new SupplierAdvanceResource(SupplierAdvance::find($supplier_advance->id));
    }

    public function update(Request $request, $Id)
    {
        $userId = Auth::id();
        $supplier_advance = SupplierAdvance::find($Id);
        $request['user_id']=$userId ?? 0;
        $request['sumOf']=Str::getUAECurrency($request->Amount);
        $supplier_advance->update($request->all());
        return new SupplierAdvanceResource(SupplierAdvance::find($Id));
    }

    public function getById($Id)
    {
        return new SupplierAdvanceResource(SupplierAdvance::find($Id));
    }

    public function delete(Request $request,$Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = SupplierAdvance::find($Id);
        $update->user_id=$userId;
        $update->save();
        $supplier_advance = SupplierAdvance::withoutTrashed()->find($Id);
        if($supplier_advance->trashed())
        {
            return new SupplierAdvanceResource(SupplierAdvance::onlyTrashed()->find($Id));
        }
        else
        {
            $supplier_advance->delete();
            return new SupplierAdvanceResource(SupplierAdvance::onlyTrashed()->find($Id));
        }
    }

    public function restore($Id)
    {
        $supplier_advance = SupplierAdvance::onlyTrashed()->find($Id);
        if (!is_null($supplier_advance))
        {
            $supplier_advance->restore();
            return new SupplierAdvanceResource(SupplierAdvance::find($Id));
        }
        return new SupplierAdvanceResource(SupplierAdvance::find($Id));
    }

    public function trashed()
    {
        $supplier_advance = SupplierAdvance::onlyTrashed()->get();
        return SupplierAdvanceResource::collection($supplier_advance);
    }

    public function ActivateDeactivate($Id)
    {
        $supplier_advance = SupplierAdvance::find($Id);
        if($supplier_advance->isActive==1)
        {
            $supplier_advance->isActive=0;
        }
        else
        {
            $supplier_advance->isActive=1;
        }
        $supplier_advance->update();
        return new SupplierAdvanceResource(SupplierAdvance::find($Id));
    }

    public function supplier_advances_push($Id)
    {
        $advance = SupplierAdvance::with('supplier')->find($Id);
        $user_id = Auth::id();
        $advance->update([
            'isPushed' =>true,
            'user_id' =>$user_id,
        ]);
        ////////////////// account section ////////////////
        if ($advance)
        {
            $accountTransaction = AccountTransaction::where(
                [
                    'supplier_id'=> $advance->supplier_id,
                    'createdDate' => date('Y-m-d'),
                ])->first();
            if (!is_null($accountTransaction)) {
                if ($accountTransaction->createdDate != date('Y-m-d')) {
                    $totalCredit = $advance->Amount;
                }
                else
                {
                    $totalCredit = $accountTransaction->Credit + $advance->Amount;
                }
                $difference = $accountTransaction->Differentiate + $advance->Amount;
            }
            else
            {
                $accountTransaction = AccountTransaction::where(
                    [
                        'supplier_id'=> $advance->supplier_id,
                    ])->get();
                $totalCredit = $advance->Amount;
                $difference = $accountTransaction->last()->Differentiate + $advance->Amount;
            }
            $AccData =
                [
                    'supplier_id' => $advance->supplier_id,
                    'Credit' => $totalCredit,
                    'Differentiate' => $difference,
                    'createdDate' => date('Y-m-d'),
                    'user_id' => $user_id,
                ];
            AccountTransaction::updateOrCreate(
                [
                    'createdDate'   => date('Y-m-d'),
                    'supplier_id'   => $advance->supplier_id,
                ],
                $AccData);
        }
        ////////////////// end of account section ////////////////
        return TRUE;
    }
}
