<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\ISupplierPaymentRepositoryInterface;
use App\Http\Resources\SupplierPayment\SupplierPaymentResource;
use App\Models\AccountTransaction;
use App\Models\Bank;
use App\Models\PaymentType;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SupplierPaymentRepository implements ISupplierPaymentRepositoryInterface
{

    public function all()
    {
        return SupplierPaymentResource::collection(SupplierPayment::with('user')->get()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return SupplierPaymentResource::Collection(SupplierPayment::with('supplier_payment_details')->get()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(Request $request)
    {
        $userId = Auth::id();
        $supplier_payment = new SupplierPayment();
        $supplier_payment->totalAmount=$request->totalAmount;
        $supplier_payment->paidAmount=$request->paidAmount;
        $supplier_payment->amountInWords=Str::getUAECurrency($request->paidAmount);
        $supplier_payment->supplier_id=$request->supplier_id;
        $supplier_payment->bank_id=$request->bank_id;
        $supplier_payment->accountNumber=$request->accountNumber;
        $supplier_payment->transferDate=$request->transferDate;
        $supplier_payment->payment_type=$request->payment_type;
        $supplier_payment->referenceNumber=$request->referenceNumber;
        $supplier_payment->receiverName=$request->receiverName;
        $supplier_payment->receiptNumber=$request->receiptNumber;
        $supplier_payment->Description=$request->Description;
        $supplier_payment->supplierPaymentDate=$request->supplierPaymentDate;
        $supplier_payment->createdDate=date('Y-m-d h:i:s');
        $supplier_payment->isActive=1;
        $supplier_payment->user_id = $userId ?? 0;
        $supplier_payment->company_id=Str::getCompany($userId);
        $supplier_payment->save();
        $supplier_payment_id = $supplier_payment->id;

        $supplier_payment_details=json_decode($_POST['supplier_payment_details']);

        $amount = 0;
        foreach ($supplier_payment_details as $payment_item)
        {
            $amount += $payment_item->amountPaid;

            if ($amount <= $request->paidAmount)
            {
                $isPaid = true;
                $isPartialPaid = false;
                $totalAmount = $payment_item->amountPaid;
            }
            elseif($amount >= $request->paidAmount){
                $isPaid = false;
                $isPartialPaid = true;
                $totalAmount1 = $amount - $request->paidAmount;
                $totalAmount = $payment_item->amountPaid - $totalAmount1;
            }

            SupplierPaymentDetail::create([
                "amountPaid" => $totalAmount,
                "purchase_id" => $payment_item->purchase_id,
                "company_id" => Str::getCompany($userId),
                "user_id" => $userId,
                "supplier_payment_id" => $supplier_payment_id,
                'createdDate' => date('Y-m-d')
            ]);


            $purchase = Purchase::find($payment_item->purchase_id);
            $purchase->update([
                "paidBalance" => $totalAmount + $purchase->paidBalance,
                "remainingBalance" => $purchase->remainingBalance - $totalAmount,
                "IsPaid" => $isPaid,
                "IsPartialPaid" => $isPartialPaid,
                //"IsReturn" => false,
                //"IsPartialReturn" => false,
                "IsNeedStampOrSignature" => false,
            ]);
        }

        $Response = SupplierPaymentResource::collection(SupplierPayment::where('id',$supplier_payment->id)->with('supplier_payment_details')->get());
        $data = json_decode(json_encode($Response), true);
        return $data[0];
    }

    public function supplier_payments_push($Id)
    {
        $payments = SupplierPayment::with('supplier')->find($Id);
        $user_id = session('user_id');
        $payments->update([
            'isPushed' =>true,
            'user_id' =>$user_id,
        ]);

        ////////////////// account section ////////////////
        if ($payments)
        {
            $accountTransaction = AccountTransaction::where(
                [
                    'supplier_id'=> $payments->supplier_id,
                    'createdDate' => date('Y-m-d'),
                ])->first();
            if (!is_null($accountTransaction)) {
                if ($accountTransaction->createdDate != date('Y-m-d')) {
                    $totalCredit = $payments->paidAmount;
                }
                else
                {
                    $totalCredit = $accountTransaction->Credit + $payments->paidAmount;
                }
                $difference = $accountTransaction->Differentiate + $payments->paidAmount;
            }
            else
            {
                $accountTransaction = AccountTransaction::where(
                    [
                        'supplier_id'=> $payments->supplier_id,
                    ])->get();
                $totalCredit = $payments->paidAmount;
                $difference = $accountTransaction->last()->Differentiate + $payments->paidAmount;
            }
            $AccData =
                [
                    'supplier_id' => $payments->supplier_id,
                    'Credit' => $totalCredit,
                    'Differentiate' => $difference,
                    'createdDate' => date('Y-m-d'),
                    'user_id' => $user_id,
                ];
            AccountTransaction::updateOrCreate(
                [
                    'createdDate'   => date('Y-m-d'),
                    'supplier_id'   => $payments->supplier_id,
                ],
                $AccData);
        }
        ////////////////// end of account section ////////////////
        return TRUE;
    }

    public function BaseList()
    {
        return array('supplier'=>Supplier::select('id','Name')->orderBy('id','desc')->get(),'payment_types'=>PaymentType::select('id','Name')->orderBy('id','desc')->get(),'banks'=>Bank::select('id','Name')->orderBy('id','desc')->get());
    }
}
