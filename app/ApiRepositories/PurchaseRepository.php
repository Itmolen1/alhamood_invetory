<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\IPurchaseRepositoryInterface;
use App\Http\Requests\PurchaseRequest;
use App\Http\Resources\Purchase\PurchaseResource;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseRepository implements IPurchaseRepositoryInterface
{

    public function all()
    {
        return PurchaseResource::collection(Purchase::with('user','purchase_details')->get()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return PurchaseResource::Collection(Purchase::all()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(Request $request)
    {
        $purchase_detail=$request->purchase_detail;

        $userId = Auth::id();
        $purchase = new Purchase();
        $purchase->supplier_id=$request->supplier_id;
        $purchase->employee_id=$request->employee_id;
        $purchase->PurchaseDate=$request->PurchaseDate;
        $purchase->DueDate=$request->DueDate;
        $purchase->referenceNumber=$request->referenceNumber;
        $purchase->Total=$request->Total;
        $purchase->subTotal=$request->subTotal;
        $purchase->totalVat=$request->totalVat;
        $purchase->grandTotal=$request->grandTotal;
        $purchase->Description=$request->Description;
        $purchase->TermsAndCondition=$request->TermsAndCondition;
        $purchase->supplierNote=$request->supplierNote;
        $purchase->IsNeedStampOrSignature=$request->IsNeedStampOrSignature;
        $purchase->createdDate=date('Y-m-d h:i:s');
        $purchase->isActive=1;
        $purchase->user_id = $userId ?? 0;
        $purchase->save();
        $purchase_id = $purchase->id;

        foreach ($purchase_detail as $purchase_item)
        {
            $data=PurchaseDetail::create([
                'purchase_id'=>$purchase_id,
                'PadNumber'=>$purchase_item['PadNumber'],
                'Price'=>$purchase_item['Price'],
                'Quantity'=>$purchase_item['Quantity'],
                'rowTotal'=>$purchase_item['rowTotal'],
                'VAT'=>$purchase_item['VAT'],
                'rowVatAmount'=>$purchase_item['rowVatAmount'],
                'rowSubTotal'=>$purchase_item['rowSubTotal'],
                'Description'=>$purchase_item['Description'],
            ]);
        }

        return new PurchaseResource(Purchase::find($purchase->id));
    }

    public function update(PurchaseRequest $purchaseRequest, $Id)
    {
        $userId = Auth::id();
        $supplier = Purchase::find($Id);
        $purchaseRequest['user_id']=$userId ?? 0;
        $supplier->update($purchaseRequest->all());
        return new PurchaseResource(Purchase::find($Id));
    }

    public function getById($Id)
    {
        return new PurchaseResource(Purchase::find($Id));
    }

    public function delete(Request $request, $Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = Purchase::find($Id);
        $update->user_id=$userId;
        $update->save();
        $supplier = Purchase::withoutTrashed()->find($Id);
        if($supplier->trashed())
        {
            return new PurchaseResource(Purchase::onlyTrashed()->find($Id));
        }
        else
        {
            $supplier->delete();
            return new PurchaseResource(Purchase::onlyTrashed()->find($Id));
        }
    }

    public function restore($Id)
    {
        $supplier = Purchase::onlyTrashed()->find($Id);
        if (!is_null($supplier))
        {
            $supplier->restore();
            return new PurchaseResource(Purchase::find($Id));
        }
        return new PurchaseResource(Purchase::find($Id));
    }

    public function trashed()
    {
        $supplier = Purchase::onlyTrashed()->get();
        return PurchaseResource::collection($supplier);
    }
}
