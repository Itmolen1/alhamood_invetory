<?php


namespace App\WebRepositories;


use App\Http\Requests\PurchaseRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use App\Models\UpdateNote;
use App\WebRepositories\Interfaces\IPurchaseRepositoryInterface;
use Illuminate\Http\Request;

class PurchaseRepository implements IPurchaseRepositoryInterface
{

    public function index()
    {
        // TODO: Implement index() method.
        $purchases = Purchase::with('purchase_details','supplier')->get();
        return view('admin.purchase.index',compact('purchases'));
    }

    public function create()
    {
        // TODO: Implement create() method.
        $purchaseNo = $this->invoiceNumber();
        $suppliers = Supplier::all();
        $products = Product::all();
        return view('admin.purchase.create',compact('suppliers','purchaseNo','products'));
    }

    public function store(PurchaseRequest $purchaseRequest)
    {
        // TODO: Implement store() method.
        $AllRequestCount = collect($purchaseRequest->Data)->count();
        if($AllRequestCount > 0) {
            //return Response()->json($request);
            // return Response()->json($request->Data['orders']);
            //return Response()->json($request->Data['PurchaseNumber']);
            //return Response()->json($request->Data['referenceNumber']);
            $user_id = session('user_id');
            $company_id = session('company_id');
            $purchase = new Purchase();
            $purchase->PurchaseNumber = $purchaseRequest->Data['PurchaseNumber'];
            $purchase->referenceNumber = $purchaseRequest->Data['referenceNumber'];
            $purchase->PurchaseDate = $purchaseRequest->Data['PurchaseDate'];
            $purchase->DueDate =  $purchaseRequest->Data['DueDate'];
            $purchase->Total = $purchaseRequest->Data['Total'];
            $purchase->subTotal = $purchaseRequest->Data['subTotal'];
            $purchase->totalVat = $purchaseRequest->Data['totalVat'];
            $purchase->grandTotal = $purchaseRequest->Data['grandTotal'];
            $purchase->paidBalance = $purchaseRequest->Data['paidBalance'];
            $purchase->remainingBalance = $purchaseRequest->Data['remainingBalance'];
            $purchase->supplier_id = $purchaseRequest->Data['supplier_id'];
            $purchase->supplierNote = $purchaseRequest->Data['supplierNote'];
            $purchase->user_id = $user_id;
            $purchase->company_id = $company_id;
            $purchase->save();
            $purchase = $purchase->id;
            //return Response()->json($purchase);
            //$user = $sale->user_id;
            // return $sale;
            foreach($purchaseRequest->Data['orders'] as $detail)
            {
                //return $detail['Quantity'];
                //return Response()->json($detail['Quantity']);


                $data =  PurchaseDetail::create([
                    "product_id"        => $detail['product_id'],
                    "Quantity"        => $detail['Quantity'],
                    "Price"        => $detail['Price'],
                    "rowTotal"        => $detail['rowTotal'],
                    "VAT"        => $detail['Vat'],
                    "rowVatAmount"        => $detail['rowVatAmount'],
                    "rowSubTotal"        => $detail['rowSubTotal'],
                    "PadNumber"        => $detail['PadNumber'],
                    "Description"        => $detail['description'],
                    "company_id" => $company_id,
                    "user_id"      => $user_id,
                    "purchase_id"      => $purchase,
                    "createdDate" => $detail['createdDate'],
                ]);

            }
            if ($data)
            {
                return Response()->json($data);
            }
        }
    }

    public function update(Request $request, $Id)
    {
        // TODO: Implement update() method.
        $AllRequestCount = collect($request->Data)->count();
        if($AllRequestCount > 0)
        {
            //return Response()->json($request);
            // return Response()->json($request->Data['orders']);
            //return Response()->json($request->Data['PurchaseNumber']);
            //return Response()->json($request->Data['referenceNumber']);

            $purchased = Purchase::find($Id);
            $user_id = session('user_id');
            $company_id = session('company_id');
            //return $request;
            $purchased->update(
                [
                    'PurchaseNumber' => $request->Data['PurchaseNumber'],
                    'referenceNumber' => $request->Data['referenceNumber'],
                    'PurchaseDate' => $request->Data['PurchaseDate'],
                    'DueDate' => $request->Data['DueDate'],
                    'Total' => $request->Data['Total'],
                    'subTotal' => $request->Data['subTotal'],
                    'totalVat' => $request->Data['totalVat'],
                    'grandTotal' => $request->Data['grandTotal'],
                    'paidBalance' => $request->Data['paidBalance'],
                    'remainingBalance' => $request->Data['remainingBalance'],
                    'supplier_id' => $request->Data['supplier_id'],
                    'supplierNote' => $request->Data['supplierNote'],
                    'user_id' => $user_id,
                    'company_id' => $company_id,
                ]);

            $update_note = new UpdateNote();
            $update_note->RelationTable = 'purchases';
            $update_note->RelationId = $Id;
            $update_note->Description = $request->Data['UpdateDescription'];
            $update_note->user_id = $user_id;
            $update_note->company_id = $company_id;
            $update_note->save();

            $d = PurchaseDetail::where('purchase_id', array($Id))->delete();
            $slct = PurchaseDetail::where('purchase_id', $Id)->get();
            foreach ($request->Data['orders'] as $detail)
            {
                $purchaseDetails = PurchaseDetail::create([
                    //"Id" => $detail['Id'],
                    "product_id"        => $detail['product_id'],
                    "Quantity"        => $detail['Quantity'],
                    "Price"        => $detail['Price'],
                    "rowTotal"        => $detail['rowTotal'],
                    "VAT"        => $detail['Vat'],
                    "rowVatAmount"        => $detail['rowVatAmount'],
                    "rowSubTotal"        => $detail['rowSubTotal'],
                    "PadNumber"        => $detail['PadNumber'],
                    "Description"        => $detail['description'],
                    "user_id"      => $user_id,
                    "company_id"      => $company_id,
                    "purchase_id"      => $Id,
                    "createdDate" => $detail['createdDate'],
                ]);
            }
            $ss = PurchaseDetail::where('purchase_id', array($purchaseDetails['purchase_id']))->get();
            return Response()->json($ss);

        }

    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        // TODO: Implement edit() method.
        $update_notes = UpdateNote::with('company','user')->where('RelationId',$Id)->get();
        //dd($update_notes[0]->Description);
        $suppliers = Supplier::all();
        $products = Product::all();
        $purchase_details = PurchaseDetail::withTrashed()->with('purchase.supplier','user','product.unit')->where('purchase_Id', $Id)->get();
        return view('admin.purchase.edit',compact('purchase_details','suppliers','products','update_notes'));
    }

    public function delete(Request $request, $Id)
    {
        // TODO: Implement delete() method.
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
    }

    public function invoiceNumber()
    {
        // TODO: Implement invoiceNumber() method.

        $invoice = new Purchase();
        $lastInvoiceID = $invoice->orderByDesc('id')->pluck('id')->first();
        $newInvoiceID = 'PUR-00'.($lastInvoiceID + 1);
        return $newInvoiceID;
    }
}
