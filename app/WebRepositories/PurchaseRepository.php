<?php


namespace App\WebRepositories;


use App\Http\Requests\PurchaseRequest;
use App\Http\Resources\Purchase\PurchaseResource;
use App\Models\AccountTransaction;
use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Unit;
use App\Models\Supplier;
use App\Models\UpdateNote;
use App\WebRepositories\Interfaces\IPurchaseRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PDF;

class PurchaseRepository implements IPurchaseRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(Purchase::with('purchase_details_without_trash.product','supplier')->where('company_id',session('company_id'))->where('isActive',1)->latest()->get())
                ->addColumn('action', function ($data) {
                    $button = '<a href="'.route('purchases.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
//                    $button .='<a href="javascript:void(0)"  onclick="return'. get_pdf($data->id).'"  class=" btn btn-secondary btn-sm"><i style="font-size: 20px" class="fa fa-file-pdf-o"></i></a>';
                    return $button;
                })
                ->addColumn('createdDate', function($data) {
                    return $data->purchase_details_without_trash[0]->createdDate ?? "No date";
                })
                ->addColumn('PadNumber', function($data) {
                    return $data->purchase_details_without_trash[0]->PadNumber ?? "No Pad";
                })
                ->addColumn('supplier', function($data) {
                    return $data->supplier->Name ?? "No Name";
                })
                ->addColumn('Product', function($data) {
                    return $data->purchase_details_without_trash[0]->product->Name ?? "No product";
                })
                ->addColumn('Quantity', function($data) {
                    return $data->purchase_details_without_trash[0]->Quantity ?? "No Quantity";
                })
                ->addColumn('Price', function($data) {
                    return $data->purchase_details_without_trash[0]->Price ?? "No Quantity";
                })
                ->rawColumns(
                    [
                        'action',
                        // 'isActive',
                        'createdDate',
                        'referenceNumber',
                        'PadNumber',
                        'supplier',
                        'Quantity',
                        'Price'
                    ])
                ->make(true);
        }
        return view('admin.purchase.index');
    }

    public function create()
    {
        $purchaseNo = $this->invoiceNumber();
        $PadNumber = $this->PadNumber();
        $suppliers = Supplier::get();
        $products = Product::all();
        return view('admin.purchase.create',compact('suppliers','purchaseNo','products','PadNumber'));
    }

    public function store(PurchaseRequest $purchaseRequest)
    {
        $AllRequestCount = collect($purchaseRequest->Data)->count();
        if($AllRequestCount > 0) {

            $user_id = session('user_id');
            $company_id = session('company_id');
            $purchase = new Purchase();

            if(isset($purchaseRequest->Data['orders'][0]['PadNumber']))
            {
                $this_pad_no=$purchaseRequest->Data['orders'][0]['PadNumber'];
            }
            else
            {
                $this_pad_no=0;
            }

            if($purchaseRequest->Data['remainingBalance']<0)
            {
                if($purchaseRequest->Data['paidBalance'] >= $purchaseRequest->Data['grandTotal'])
                {
                    if ($purchaseRequest->Data['paidBalance'] == 0.00 || $purchaseRequest->Data['paidBalance'] == 0) {
                        $isPaid_current = false;
                        $partialPaid_current =false;
                    }
                    elseif($purchaseRequest->Data['paidBalance'] >= $purchaseRequest->Data['grandTotal'])
                    {
                        $isPaid_current = 1;
                        $partialPaid_current=0;
                        // if the paidBalance = cashPaid is more than grand total we need to divide extra
                        // amount to unpaid sales if its there any entry
                        $all_purchase = Purchase::with('supplier','purchase_details')->where([
                            'supplier_id'=>$purchaseRequest->Data['supplier_id'],
                            'IsPaid'=> false,
                        ])->orderBy('PurchaseDate')->get();
                        //dd($all_purchase);
                        $total_i_have=$purchaseRequest->Data['paidBalance']-$purchaseRequest->Data['grandTotal'];

                        foreach($all_purchase as $purchase)
                        {
                            $total_you_need = $purchase->remainingBalance;
                            $still_payable_to_you=0;
                            $total_giving_to_you=0;
                            $isPartialPaid = 0;
                            if ($total_i_have >= $total_you_need)
                            {
                                $isPaid = 1;
                                $isPartialPaid = 0;
                                $total_i_have = $total_i_have - $total_you_need;

                                $this_sale = Purchase::find($purchase->id);
                                $this_sale->update([
                                    "paidBalance"        => $purchase->grandTotal,
                                    "remainingBalance"   => $still_payable_to_you,
                                    "IsPaid" => $isPaid,
                                    "IsPartialPaid" => $isPartialPaid,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid',
                                    "account_transaction_payment_id" => 'CashOverflow',
                                ]);
                            }
                            else
                            {
                                $isPaid = 0;
                                $isPartialPaid = 1;
                                $total_giving_to_you=$total_i_have;
                                $total_i_have = $total_i_have - $total_giving_to_you;

                                $this_purchase = Purchase::find($purchase->id);
                                $this_purchase->update([
                                    "paidBalance"        => $purchase->paidBalance+$total_giving_to_you,
                                    "remainingBalance"   => $purchase->remainingBalance-$total_giving_to_you,
                                    "IsPaid" => $isPaid,
                                    "IsPartialPaid" => $isPartialPaid,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid',
                                    "account_transaction_payment_id" => 'CashOverflow',
                                ]);
                            }

                            if($total_i_have<=0)
                            {
                                break;
                            }
                        }
                    }
                    else
                    {
                        $isPaid_current = false;
                        $partialPaid_current =true;
                    }

                    $purchase = new Purchase();
                    $purchase->PurchaseNumber = $purchaseRequest->Data['PurchaseNumber'];
                    $purchase->referenceNumber = $purchaseRequest->Data['referenceNumber'];
                    $purchase->PurchaseDate = $purchaseRequest->Data['PurchaseDate'];
                    $purchase->DueDate =  $purchaseRequest->Data['DueDate'];
                    $purchase->Total = $purchaseRequest->Data['Total'];
                    $purchase->subTotal = $purchaseRequest->Data['subTotal'];
                    $purchase->totalVat = $purchaseRequest->Data['totalVat'];
                    $purchase->grandTotal = $purchaseRequest->Data['grandTotal'];

                    if($purchaseRequest->Data['lastClosing']<0 && $purchaseRequest->Data['paidBalance']==0 || $purchaseRequest->Data['paidBalance']==0.00)
                    {
                        $purchase->paidBalance = ($purchaseRequest->Data['grandTotal']-$purchaseRequest->Data['paidBalance']-$purchaseRequest->Data['remainingBalance']);
                        $purchase->remainingBalance = $purchaseRequest->Data['remainingBalance'];
                        $isPaid_current = false;
                        $partialPaid_current =true;
                    }
                    elseif($purchaseRequest->Data['lastClosing']<0)
                    {
                        $purchase->paidBalance = ($purchaseRequest->Data['grandTotal']-$purchaseRequest->Data['paidBalance']-$purchaseRequest->Data['lastClosing']);
                        $purchase->remainingBalance = $purchaseRequest->Data['remainingBalance'];
                    }
                    else
                    {
                        $purchase->paidBalance = $purchaseRequest->Data['grandTotal'];
                        $purchase->remainingBalance = 0;
                    }
                    $purchase->supplier_id = $purchaseRequest->Data['supplier_id'];
                    $purchase->Description = 'AutoPaid';
                    $purchase->supplierNote = $purchaseRequest->Data['supplierNote'];
                    $purchase->IsPaid = $isPaid_current;
                    $purchase->IsPartialPaid = $partialPaid_current;
                    $purchase->IsNeedStampOrSignature = false;
                    $purchase->user_id = $user_id;
                    $purchase->company_id = $company_id;
                    $purchase->save();
                    $purchase = $purchase->id;

                    foreach($purchaseRequest->Data['orders'] as $detail)
                    {
                        //return $detail['Quantity'];
                        //return Response()->json($detail['Quantity']);
                        $data =  PurchaseDetail::create([
                            "product_id"        => $detail['product_id'],
                            "unit_id"        => $detail['unit_id'],
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
                            "createdDate" => $purchaseRequest->Data['PurchaseDate'],
                            "supplier_id" => $purchaseRequest->Data['supplier_id'],
                        ]);
                    }
                }
                else
                {
                    $isPaid = true;
                    $partialPaid =false;

                    $purchase = new Purchase();
                    $purchase->PurchaseNumber = $purchaseRequest->Data['PurchaseNumber'];
                    $purchase->referenceNumber = $purchaseRequest->Data['referenceNumber'];
                    $purchase->PurchaseDate = $purchaseRequest->Data['PurchaseDate'];
                    $purchase->DueDate =  $purchaseRequest->Data['DueDate'];
                    $purchase->Total = $purchaseRequest->Data['Total'];
                    $purchase->subTotal = $purchaseRequest->Data['subTotal'];
                    $purchase->totalVat = $purchaseRequest->Data['totalVat'];
                    $purchase->grandTotal = $purchaseRequest->Data['grandTotal'];
                    $purchase->paidBalance = $purchaseRequest->Data['grandTotal'];
                    $purchase->remainingBalance = 0;
                    $purchase->Description = 'AutoPaid';
                    //$purchase->remainingBalance = $purchaseRequest->Data['remainingBalance'];
                    $purchase->supplier_id = $purchaseRequest->Data['supplier_id'];
                    $purchase->supplierNote = $purchaseRequest->Data['supplierNote'];
                    $purchase->IsPaid = $isPaid;
                    $purchase->IsPartialPaid = $partialPaid;
                    $purchase->IsNeedStampOrSignature = false;
                    $purchase->user_id = $user_id;
                    $purchase->company_id = $company_id;
                    $purchase->save();
                    $purchase = $purchase->id;

                    foreach($purchaseRequest->Data['orders'] as $detail)
                    {
                        $data =  PurchaseDetail::create([
                            "product_id"        => $detail['product_id'],
                            "unit_id"        => $detail['unit_id'],
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
                            "createdDate" => $purchaseRequest->Data['PurchaseDate'],
                            "supplier_id" => $purchaseRequest->Data['supplier_id'],
                        ]);
                    }
                }
            }
            else
            {
                if($purchaseRequest->Data['remainingBalance']>0 && $purchaseRequest->Data['lastClosing']<0)
                {
                    if ($purchaseRequest->Data['paidBalance'] == 0.00 || $purchaseRequest->Data['paidBalance'] == 0) {
                        $isPaid_current = false;
                        $partialPaid_current =false;
                    }
                    elseif($purchaseRequest->Data['paidBalance'] >= $purchaseRequest->Data['grandTotal'])
                    {
                        $isPaid_current = 1;
                        $partialPaid_current=0;
                        // if the paidBalance = cashPaid is more than grand total we need to divide extra
                        // amount to unpaid sales if its there any entry
                        $all_purchase = Purchase::with('supplier','purchase_details')->where([
                            'supplier_id'=>$purchaseRequest->Data['supplier_id'],
                            'IsPaid'=> false,
                        ])->orderBy('PurchaseDate')->get();
                        //dd($all_purchase);
                        $total_i_have=$purchaseRequest->Data['paidBalance']-$purchaseRequest->Data['grandTotal'];

                        foreach($all_purchase as $purchase)
                        {
                            $total_you_need = $purchase->remainingBalance;
                            $still_payable_to_you=0;
                            $total_giving_to_you=0;
                            $isPartialPaid = 0;
                            if ($total_i_have >= $total_you_need)
                            {
                                $isPaid = 1;
                                $isPartialPaid = 0;
                                $total_i_have = $total_i_have - $total_you_need;

                                $this_sale = Purchase::find($purchase->id);
                                $this_sale->update([
                                    "paidBalance"        => $purchase->grandTotal,
                                    "remainingBalance"   => $still_payable_to_you,
                                    "IsPaid" => $isPaid,
                                    "IsPartialPaid" => $isPartialPaid,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid',
                                    "account_transaction_payment_id" => 'CashOverflow',
                                ]);
                            }
                            else
                            {
                                $isPaid = 0;
                                $isPartialPaid = 1;
                                $total_giving_to_you=$total_i_have;
                                $total_i_have = $total_i_have - $total_giving_to_you;

                                $this_purchase = Purchase::find($purchase->id);
                                $this_purchase->update([
                                    "paidBalance"        => $purchase->paidBalance+$total_giving_to_you,
                                    "remainingBalance"   => $purchase->remainingBalance-$total_giving_to_you,
                                    "IsPaid" => $isPaid,
                                    "IsPartialPaid" => $isPartialPaid,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid',
                                    "account_transaction_payment_id" => 'CashOverflow',
                                ]);
                            }

                            if($total_i_have<=0)
                            {
                                break;
                            }
                        }
                    }
                    else
                    {
                        $isPaid_current = false;
                        $partialPaid_current =true;
                    }
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

                    if($purchaseRequest->Data['lastClosing']<0 && $purchaseRequest->Data['paidBalance']==0 || $purchaseRequest->Data['paidBalance']==0.00)
                    {
                        $purchase->paidBalance = ($purchaseRequest->Data['grandTotal']-$purchaseRequest->Data['paidBalance']-$purchaseRequest->Data['remainingBalance']);
                        $purchase->remainingBalance = $purchaseRequest->Data['remainingBalance'];
                        $isPaid_current = false;
                        $partialPaid_current =true;
                    }
                    elseif($purchaseRequest->Data['lastClosing']<0 && $purchaseRequest->Data['paidBalance'] <= $purchaseRequest->Data['grandTotal'])
                    {
                        $purchase->paidBalance = $purchaseRequest->Data['paidBalance']-$purchaseRequest->Data['lastClosing'];
                        $purchase->remainingBalance = $purchaseRequest->Data['remainingBalance'];
                    }
                    else
                    {
                        $purchase->paidBalance = $purchaseRequest->Data['grandTotal'];
                        $purchase->remainingBalance = 0;
                    }
                    $purchase->supplier_id = $purchaseRequest->Data['supplier_id'];
                    $purchase->Description = 'AutoPaid';
                    $purchase->supplierNote = $purchaseRequest->Data['supplierNote'];
                    $purchase->IsPaid = $isPaid_current;
                    $purchase->IsPartialPaid = $partialPaid_current;
                    $purchase->IsNeedStampOrSignature = false;
                    $purchase->user_id = $user_id;
                    $purchase->company_id = $company_id;
                    $purchase->save();
                    $purchase = $purchase->id;

                    foreach($purchaseRequest->Data['orders'] as $detail)
                    {
                        $data =  PurchaseDetail::create([
                            "product_id"        => $detail['product_id'],
                            "unit_id"        => $detail['unit_id'],
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
                            "createdDate" => $purchaseRequest->Data['PurchaseDate'],
                            "supplier_id" => $purchaseRequest->Data['supplier_id'],
                        ]);
                    }
                }
                else
                {
                    if ($purchaseRequest->Data['paidBalance'] == 0.00 || $purchaseRequest->Data['paidBalance'] == 0) {
                        $isPaid_current = false;
                        $partialPaid_current =false;
                    }
                    elseif($purchaseRequest->Data['paidBalance'] >= $purchaseRequest->Data['grandTotal'])
                    {
                        $isPaid_current = 1;
                        $partialPaid_current=0;
                        // if the paidBalance = cashPaid is more than grand total we need to divide extra
                        // amount to unpaid sales if its there any entry
                        $all_purchase = Purchase::with('supplier','purchase_details')->where([
                            'supplier_id'=>$purchaseRequest->Data['supplier_id'],
                            'IsPaid'=> false,
                        ])->orderBy('PurchaseDate')->get();
                        //dd($all_purchase);
                        $total_i_have=$purchaseRequest->Data['paidBalance']-$purchaseRequest->Data['grandTotal'];

                        foreach($all_purchase as $purchase)
                        {
                            $total_you_need = $purchase->remainingBalance;
                            $still_payable_to_you=0;
                            $total_giving_to_you=0;
                            $isPartialPaid = 0;
                            if ($total_i_have >= $total_you_need)
                            {
                                $isPaid = 1;
                                $isPartialPaid = 0;
                                $total_i_have = $total_i_have - $total_you_need;

                                $this_sale = Purchase::find($purchase->id);
                                $this_sale->update([
                                    "paidBalance"        => $purchase->grandTotal,
                                    "remainingBalance"   => $still_payable_to_you,
                                    "IsPaid" => $isPaid,
                                    "IsPartialPaid" => $isPartialPaid,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid',
                                    "account_transaction_payment_id" => 'CashOverflow',
                                ]);
                            }
                            else
                            {
                                $isPaid = 0;
                                $isPartialPaid = 1;
                                $total_giving_to_you=$total_i_have;
                                $total_i_have = $total_i_have - $total_giving_to_you;

                                $this_purchase = Purchase::find($purchase->id);
                                $this_purchase->update([
                                    "paidBalance"        => $purchase->paidBalance+$total_giving_to_you,
                                    "remainingBalance"   => $purchase->remainingBalance-$total_giving_to_you,
                                    "IsPaid" => $isPaid,
                                    "IsPartialPaid" => $isPartialPaid,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid',
                                    "account_transaction_payment_id" => 'CashOverflow',
                                ]);
                            }

                            if($total_i_have<=0)
                            {
                                break;
                            }
                        }
                    }
                    else
                    {
                        $isPaid_current = false;
                        $partialPaid_current =true;
                    }

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

                    if($purchaseRequest->Data['lastClosing']<0 )
                    {
                        $purchase->paidBalance = ($purchaseRequest->Data['grandTotal']-$purchaseRequest->Data['paidBalance']-$purchaseRequest->Data['lastClosing']);
                        $purchase->remainingBalance = $purchaseRequest->Data['remainingBalance'];
                    }
                    else
                    {
                        $purchase->paidBalance = $purchaseRequest->Data['paidBalance'];
                        $purchase->remainingBalance = $purchaseRequest->Data['grandTotal']-$purchaseRequest->Data['paidBalance'];
                    }
                    $purchase->supplier_id = $purchaseRequest->Data['supplier_id'];
                    $purchase->Description = 'AutoPaid';
                    $purchase->supplierNote = $purchaseRequest->Data['supplierNote'];
                    $purchase->IsPaid = $isPaid_current;
                    $purchase->IsPartialPaid = $partialPaid_current;
                    $purchase->IsNeedStampOrSignature = false;
                    $purchase->user_id = $user_id;
                    $purchase->company_id = $company_id;
                    $purchase->save();
                    $purchase = $purchase->id;

                    foreach($purchaseRequest->Data['orders'] as $detail)
                    {
                        $data =  PurchaseDetail::create([
                            "product_id"        => $detail['product_id'],
                            "unit_id"        => $detail['unit_id'],
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
                            "createdDate" => $purchaseRequest->Data['PurchaseDate'],
                            "supplier_id" => $purchaseRequest->Data['supplier_id'],
                        ]);
                    }
                }
            }

            if($purchaseRequest->Data['paidBalance'] != 0.00 || $purchaseRequest->Data['paidBalance'] != 0)
            {
                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                $difference = $cashTransaction->last()->Differentiate;
                $cash_transaction = new CashTransaction();
                $cash_transaction->Reference=$purchase;
                $cash_transaction->createdDate=$purchaseRequest->Data['PurchaseDate'];
                $cash_transaction->Type='purchases';
                $cash_transaction->Details='CashPurchase|'.$purchase;
                $cash_transaction->Credit=$purchaseRequest->Data['paidBalance'];
                $cash_transaction->Debit=0.00;
                $cash_transaction->Differentiate=$difference-$purchaseRequest->Data['paidBalance'];
                $cash_transaction->user_id = $user_id;
                $cash_transaction->company_id = $company_id;
                $cash_transaction->save();
            }

            ////////////////// start account section gautam ////////////////
            if ($purchase)
            {
                $accountTransaction = AccountTransaction::where(['supplier_id'=> $purchaseRequest->Data['supplier_id'],])->get();
                // totally credit
                if ($purchaseRequest->Data['paidBalance'] == 0 || $purchaseRequest->Data['paidBalance'] == 0.00) {
                    $totalCredit = $purchaseRequest->Data['grandTotal'];
                    $difference = $accountTransaction->last()->Differentiate + $purchaseRequest->Data['grandTotal'];

                    $AccData =
                        [
                            'supplier_id' => $purchaseRequest->Data['supplier_id'],
                            'Credit' => $totalCredit,
                            'Debit' => 0.00,
                            'Differentiate' => $difference,
                            'createdDate' => $purchaseRequest->Data['PurchaseDate'],
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'Purchase|'.$purchase,
                            'referenceNumber'=>$this_pad_no,
                        ];
                    $AccountTransactions = AccountTransaction::Create($AccData);
                }
                // partial payment some cash some credit
                elseif($purchaseRequest->Data['paidBalance'] > 0 AND $purchaseRequest->Data['paidBalance'] < $purchaseRequest->Data['grandTotal'] )
                {
                    $differenceValue = $accountTransaction->last()->Differentiate - $purchaseRequest->Data['paidBalance'];
                    $totalCredit = $accountTransaction->last()->Differentiate + $purchaseRequest->Data['grandTotal'];
                    $difference = $differenceValue + $purchaseRequest->Data['grandTotal'];

                    //make credit entry for the purchase
                    $AccData =
                        [
                            'supplier_id' => $purchaseRequest->Data['supplier_id'],
                            'Credit' => $purchaseRequest->Data['grandTotal'],
                            'Debit' => 0.00,
                            'Differentiate' => $totalCredit,
                            'createdDate' => $purchaseRequest->Data['PurchaseDate'],
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'Purchase|'.$purchase,
                            'referenceNumber'=>$this_pad_no,
                        ];
                    $AccountTransactions = AccountTransaction::Create($AccData);

                    //make debit entry for the whatever cash is paid
                    $difference=$totalCredit-$purchaseRequest->Data['paidBalance'];
                    $AccData =
                        [
                            'supplier_id' => $purchaseRequest->Data['supplier_id'],
                            'Credit' => 0.00,
                            'Debit' => $purchaseRequest->Data['paidBalance'],
                            'Differentiate' => $difference,
                            'createdDate' => $purchaseRequest->Data['PurchaseDate'],
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'PartialCashPurchase|'.$purchase,
                            'referenceNumber'=>$this_pad_no,
                        ];
                    $AccountTransactions = AccountTransaction::Create($AccData);
                }
                // fully paid with cash
                else
                {
                    $totalCredit = $purchaseRequest->Data['grandTotal'];
                    $difference = $accountTransaction->last()->Differentiate + $purchaseRequest->Data['grandTotal'];

                    //make credit entry for the purchase
                    $AccountTransactions=AccountTransaction::Create([
                        'supplier_id' => $purchaseRequest->Data['supplier_id'],
                        'Credit' => $totalCredit,
                        'Debit' => 0.00,
                        'Differentiate' => $difference,
                        'createdDate' => $purchaseRequest->Data['PurchaseDate'],
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description'=>'Purchase|'.$purchase,
                        'referenceNumber'=>$this_pad_no,
                    ]);

                    //make debit entry for the whatever cash is paid
                    $difference=$difference-$purchaseRequest->Data['paidBalance'];
                    $AccountTransactions=AccountTransaction::Create([
                        'supplier_id' => $purchaseRequest->Data['supplier_id'],
                        'Credit' => 0.00,
                        'Debit' => $purchaseRequest->Data['paidBalance'],
                        'Differentiate' => $difference,
                        'createdDate' => $purchaseRequest->Data['PurchaseDate'],
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description'=>'FullCashPurchase|'.$purchase,
                        'referenceNumber'=>$this_pad_no,
                    ]);
                }
                return Response()->json($AccountTransactions);
                // return Response()->json("");
            }
            ////////////////// end account section gautam ////////////////

//            ////////////////// account section ////////////////
//            if ($purchase)
//            {
//                $accountTransaction = AccountTransaction::where(
//                    [
//                        'supplier_id'=> $purchaseRequest->Data['supplier_id'],
//                        'createdDate' => date('Y-m-d'),
//                    ])->first();
//                if (!is_null($accountTransaction))
//                {
//                    if ($purchaseRequest->Data['paidBalance'] == 0 || $purchaseRequest->Data['paidBalance'] == 0.00) {
//                        if ($accountTransaction->createdDate != date('Y-m-d')) {
//                            $totalDebit = $purchaseRequest->Data['grandTotal'];
//                        } else {
//                            $totalDebit = $accountTransaction->Debit + $purchaseRequest->Data['grandTotal'];
//                        }
//                        $totalCredit = $accountTransaction->Credit;
//                        $difference = $accountTransaction->Differentiate - $purchaseRequest->Data['grandTotal'];
//                    }
//                    elseif($purchaseRequest->Data['paidBalance'] > 0 AND $purchaseRequest->Data['paidBalance'] < $purchaseRequest->Data['grandTotal'] )
//                    {
//                        if ($accountTransaction->createdDate != date('Y-m-d')) {
//                            $totalCredit = $purchaseRequest->Data['paidBalance'];
//                            $totalDebit = $purchaseRequest->Data['grandTotal'];
//                        } else {
//                            $totalCredit = $accountTransaction->Credit + $purchaseRequest->Data['paidBalance'];
//                            $totalDebit = $accountTransaction->Debit + $purchaseRequest->Data['grandTotal'];
//                        }
//                        $differenceValue = $accountTransaction->Differentiate + $purchaseRequest->Data['paidBalance'];
//                        $difference = $differenceValue - $purchaseRequest->Data['grandTotal'];
//                    }
//                    else{
//
//                        if ($accountTransaction->createdDate != date('Y-m-d')) {
//                            $totalCredit = $purchaseRequest->Data['paidBalance'];
//                        } else {
//                            $totalCredit = $accountTransaction->Credit + $purchaseRequest->Data['paidBalance'];
//                        }
//                        $totalDebit = $accountTransaction->Debit;
//                        $difference = $accountTransaction->Differentiate + $purchaseRequest->Data['paidBalance'];
//                    }
//                }
//                else
//                {
//                    $accountTransaction = AccountTransaction::where(
//                        [
//                            'supplier_id'=> $purchaseRequest->Data['supplier_id'],
//                        ])->get();
//                    if ($purchaseRequest->Data['paidBalance'] == 0 || $purchaseRequest->Data['paidBalance'] == 0.00) {
//                        $totalDebit = $purchaseRequest->Data['grandTotal'];
//                        $totalCredit = $accountTransaction->last()->Credit;
//                        $difference = $accountTransaction->last()->Differentiate + $purchaseRequest->Data['grandTotal'];
//                    }
//                    elseif($purchaseRequest->Data['paidBalance'] > 0 AND $purchaseRequest->Data['paidBalance'] < $purchaseRequest->Data['grandTotal'] )
//                    {
//
//                        $totalCredit = $purchaseRequest->Data['paidBalance'];
//                        $totalDebit = $purchaseRequest->Data['grandTotal'];
//                        $differenceValue = $accountTransaction->last()->Differentiate - $purchaseRequest->Data['paidBalance'];
//                        $difference = $differenceValue + $purchaseRequest->Data['grandTotal'];
//                    }
//                    else{
//                        $totalCredit = $purchaseRequest->Data['paidBalance'];
//                        $totalDebit = $accountTransaction->last()->Debit;
//                        $difference = $accountTransaction->last()->Differentiate - $purchaseRequest->Data['paidBalance'];
//                    }
//                }
//                $AccData =
//                    [
//                        'supplier_id' => $purchaseRequest->Data['supplier_id'],
//                        'Credit' => $totalCredit,
//                        'Debit' => $totalDebit,
//                        'Differentiate' => $difference,
//                        'createdDate' => date('Y-m-d'),
//                        'user_id' => $user_id,
//                        'company_id' => $company_id,
//                    ];
//
//                $AccountTransactions = AccountTransaction::updateOrCreate(
//                    [
//                        'createdDate'   => date('Y-m-d'),
//                        'supplier_id'   => $purchaseRequest->Data['supplier_id'],
//                    ],
//                    $AccData);
//                return Response()->json($AccountTransactions);
//                // return Response()->json("");
//            }
            ////////////////// end of account section ////////////////
//            if ($data)
//            {
//                return Response()->json($data);
//            }
        }
    }

    public function update(Request $request, $Id)
    {
        $AllRequestCount = collect($request->Data)->count();
        if($AllRequestCount > 0)
        {
            $purchased = Purchase::find($Id);
            $user_id = session('user_id');
            $company_id = session('company_id');

            ////////////////// account section gautam ////////////////
            $accountTransaction = AccountTransaction::where(['supplier_id'=> $purchased->supplier_id,])->get();
            if (!is_null($accountTransaction))
            {
                // payment is done (in any way - advance or payment)
                if($purchased->IsPaid==1 && $purchased->IsPartialPaid==0)
                {
                    // if more cash incoming then need to add in supplier account

                    //check if only supplier is changed and not quantity or price = grand total is same as previous
                    if($request->Data['supplier_id']!=$purchased->supplier_id  AND $purchased->grandTotal==$request->Data['grandTotal'])
                    {
                        //supplier is changed need to reverse all previously made account entries for the previous supplier

                        // start reverse entry for wrong supplier
                        $last_closing=$accountTransaction->last()->Differentiate;
                        $description_string='Purchase|'.$Id;
                        $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                        $previously_credited = $previous_entry->Credit;
                        $AccData =
                            [
                                'supplier_id' => $purchased->supplier_id,
                                'Debit' => $previously_credited,
                                'Credit' => 0.00,
                                'Differentiate' => $last_closing-$previously_credited,
                                'createdDate' => $request->Data['PurchaseDate'],
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Purchase|'.$Id,
                                'updateDescription'=>'hide',
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // also hide previous entry start
                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                        // also hide previous entry end
                        // reverse entry done for wrong supplier

                        /*new entry*/
                        // start new entry for right supplier and credit or debit account based on closing balance
                        $accountTransaction = AccountTransaction::where(['supplier_id'=> $request->Data['supplier_id'],])->get();
                        $totalCredit = $request->Data['grandTotal'];
                        $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                        $AccData =
                            [
                                'supplier_id' => $request->Data['supplier_id'],
                                'Credit' => $totalCredit,
                                'Debit' => 0.00,
                                'Differentiate' => $difference,
                                'createdDate' => $request->Data['PurchaseDate'],
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Purchase|'.$Id,
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        if($difference<0)
                        {
                            // still there is advance amount so make it fully paid
                            $this_purchase = Purchase::find($purchased->id);
                            $this_purchase->update([
                                "paidBalance"        => $request->Data['grandTotal'],
                                "remainingBalance"   => 0.00,
                                "IsPaid" => 1,
                                "IsPartialPaid" => 0,
                                "IsNeedStampOrSignature" => false,
                                "Description" => 'AutoPaid',
                            ]);
                        }
                        elseif($difference>0)
                        {
                            if($difference==($request->Data['grandTotal']))
                            {
                                // now we are payable so differance amount will be paid amount and make it partial paid
                                $this_purchase = Purchase::find($purchased->id);
                                $this_purchase->update([
                                    "paidBalance"        => 0,
                                    "remainingBalance"   => $difference,
                                    "IsPaid" => 0,
                                    "IsPartialPaid" => 0,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid',
                                ]);
                            }
                            else
                            {
                                // now we are payable so differance amount will be paid amount and make it partial paid
                                $this_purchase = Purchase::find($purchased->id);
                                $this_purchase->update([
                                    "paidBalance"        => $request->Data['grandTotal']-$difference,
                                    "remainingBalance"   => $difference,
                                    "IsPaid" => 0,
                                    "IsPartialPaid" => 1,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid',
                                ]);
                            }
                        }
                        /*new entry*/
                    }

                    // check if only grand total is changed and not the supplier
                    elseif($request->Data['supplier_id']==$purchased->supplier_id  AND $purchased->grandTotal!=$request->Data['grandTotal'])
                    {
                        // 1 : reverse older entry
                        // start reverse entry
                        $last_closing=$accountTransaction->last()->Differentiate;
                        $description_string='Purchase|'.$Id;
                        $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                        $previously_credited = $previous_entry->Credit;
                        $AccData =
                            [
                                'supplier_id' => $purchased->supplier_id,
                                'Debit' => $previously_credited,
                                'Credit' => 0.00,
                                'Differentiate' => $last_closing-$previously_credited,
                                'createdDate' => $request->Data['PurchaseDate'],
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Purchase|'.$Id,
                                'updateDescription'=>'hide',
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // also hide previous entry start
                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                        // also hide previous entry end
                        // reverse entry done

                        /* new entry start */
                        // make new entry then check account balance
                        $accountTransaction = AccountTransaction::where(['supplier_id'=> $request->Data['supplier_id'],])->get();

                        $totalCredit = $request->Data['grandTotal'];
                        $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                        $AccData =
                            [
                                'supplier_id' => $request->Data['supplier_id'],
                                'Credit' => $totalCredit,
                                'Debit' => 0.00,
                                'Differentiate' => $difference,
                                'createdDate' => $request->Data['PurchaseDate'],
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Purchase|'.$Id,
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        $accountTransaction_ref=$AccountTransactions->id;
                        /* new entry end */

                        // if difference is positive meaning advance is over and we are payable
                        // so update purchase entry with difference amount as paid amount
                        if($difference<0)
                        {
                            // still there is advance amount so make it fully paid
                            $this_purchase = Purchase::find($purchased->id);
                            $this_purchase->update([
                                "paidBalance"        => $request->Data['grandTotal'],
                                "remainingBalance"   => 0.00,
                                "IsPaid" => 1,
                                "IsPartialPaid" => 0,
                                "IsNeedStampOrSignature" => false,
                                "Description" => 'AutoPaid',
                            ]);
                        }
                        elseif($difference>0)
                        {
                            if($difference==($request->Data['grandTotal']))
                            {
                                // now we are payable so differance amount will be paid amount and make it partial paid
                                $this_purchase = Purchase::find($purchased->id);
                                $this_purchase->update([
                                    "paidBalance"        => $request->Data['grandTotal'],
                                    "remainingBalance"   => $difference,
                                    "IsPaid" => 1,
                                    "IsPartialPaid" => 0,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid',
                                ]);
                            }
                            else
                            {
                                // now we are payable so differance amount will be paid amount and make it partial paid
                                $this_purchase = Purchase::find($purchased->id);
                                $this_purchase->update([
                                    "paidBalance"        => $request->Data['grandTotal']-$difference,
                                    "remainingBalance"   => $difference,
                                    "IsPaid" => 0,
                                    "IsPartialPaid" => 1,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid',
                                ]);
                            }
                        }
                        // fully paid case will come here
                    }

                    // check both supplier and grandTotal is changed meaning case 3
                    elseif($request->Data['supplier_id']!=$purchased->supplier_id  AND $purchased->grandTotal!=$request->Data['grandTotal'])
                    {
                        // start reverse entry for wrong supplier with wrong entries
                        $last_closing=$accountTransaction->last()->Differentiate;
                        $description_string='Purchase|'.$Id;
                        $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                        $previously_credited = $previous_entry->Credit;
                        $AccData =
                            [
                                'supplier_id' => $purchased->supplier_id,
                                'Debit' => $previously_credited,
                                'Credit' => 0.00,
                                'Differentiate' => $last_closing-$previously_credited,
                                'createdDate' => $request->Data['PurchaseDate'],
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Purchase|'.$Id,
                                'updateDescription'=>'hide',
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // also hide previous entry start
                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                        // also hide previous entry end
                        // reverse entry done for wrong supplier

                        /*new entry with right grand total */
                        // start new entry for right supplier and credit or debit account based on closing balance
                        $accountTransaction = AccountTransaction::where(['supplier_id'=> $request->Data['supplier_id'],])->get();
                        $totalCredit = $request->Data['grandTotal'];
                        $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                        $AccData =
                            [
                                'supplier_id' => $request->Data['supplier_id'],
                                'Credit' => $totalCredit,
                                'Debit' => 0.00,
                                'Differentiate' => $difference,
                                'createdDate' => $request->Data['PurchaseDate'],
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Purchase|'.$Id,
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        if($difference<0)
                        {
                            // still there is advance amount so make it fully paid
                            $this_purchase = Purchase::find($purchased->id);
                            $this_purchase->update([
                                "paidBalance"        => $request->Data['grandTotal'],
                                "remainingBalance"   => 0.00,
                                "IsPaid" => 1,
                                "IsPartialPaid" => 0,
                                "IsNeedStampOrSignature" => false,
                                "Description" => 'AutoPaid',
                            ]);
                        }
                        elseif($difference>0)
                        {
                            if($difference==($request->Data['grandTotal']))
                            {
                                // now we are payable so differance amount will be paid amount and make it partial paid
                                $this_purchase = Purchase::find($purchased->id);
                                $this_purchase->update([
                                    "paidBalance"        => $request->Data['grandTotal'],
                                    "remainingBalance"   => $difference,
                                    "IsPaid" => 1,
                                    "IsPartialPaid" => 0,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid',
                                ]);
                            }
                            else
                            {
                                // now we are payable so differance amount will be paid amount and make it partial paid
                                $this_purchase = Purchase::find($purchased->id);
                                $this_purchase->update([
                                    "paidBalance"        => $request->Data['grandTotal']-$difference,
                                    "remainingBalance"   => $difference,
                                    "IsPaid" => 0,
                                    "IsPartialPaid" => 1,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid',
                                ]);
                            }
                        }
                        /*new entry with right grand total*/
                    }
                    //return Response()->json($accountTransaction);
                }
                // payment is done (in any way - advance or payment)
                elseif($purchased->IsPaid==0 && $purchased->IsPartialPaid==1)
                {
                    // if more cash incoming then need to add in supplier account

                    //check if only supplier is changed and not quantity or price = grand total is same as previous
                    if($request->Data['supplier_id']!=$purchased->supplier_id  AND $purchased->grandTotal==$request->Data['grandTotal'])
                    {
                        //supplier is changed need to reverse all previously made account entries for the previous supplier

                        //case : 1 full credit entry + payment is not done yet like isPaid=0 and IsPartialPaid=0
                        if($purchased->IsPaid==0 && $purchased->IsPartialPaid==0)
                        {
                            // start reverse entry
                            $last_closing=$accountTransaction->last()->Differentiate;
                            $description_string='Purchase|'.$Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_credited = $previous_entry->Credit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing-$previously_credited,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Purchase|'.$Id,
                                    'updateDescription'=>'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done
                        }
                        //case : 2 partial cash is paid and some amount is credit + payment is not fully done yet like isPaid=0 and IsPartialPaid=1
                        elseif($purchased->paidBalance > 0 AND $purchased->paidBalance < $purchased->grandTotal AND $purchased->IsPartialPaid==1)
                        {
                            // entry 1 : debit entry for purchase
                            // start reverse entry
                            $last_closing=$accountTransaction->last()->Differentiate;
                            $description_string='Purchase|'.$Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_credited = $previous_entry->Credit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing-$previously_credited,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Purchase|'.$Id,
                                    'updateDescription'=>'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done
                            // entry 2 : credit whatever cash is debited
                            // start reverse entry
                            $accountTransaction = AccountTransaction::where(['supplier_id'=> $purchased->supplier_id,])->get();
                            $last_closing=$accountTransaction->last()->Differentiate;
                            $description_string='PartialCashPurchase|'.$Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_debited = $previous_entry->Debit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => 0.00,
                                    'Credit' => $previously_debited,
                                    'Differentiate' => $last_closing+$previously_debited,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'PartialCashPurchase|'.$Id,
                                    'updateDescription'=>'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done

                            // reverse cash entry start
                            // reverse cash entry end
                            // no need to make cash entries because amount is same only supplier is changing
                            // make new cash entry for correct supplier start
                            // make new cash entry for correct supplier end
                        }
                        //case : 3 fully cash paid + isPaid=1 and IsPartialPaid=1
                        else
                        {
                            // entry 1 : debit entry for purchase
                            // start reverse entry
                            $last_closing=$accountTransaction->last()->Differentiate;
                            $description_string='Purchase|'.$Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_credited = $previous_entry->Credit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing-$previously_credited,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Purchase|'.$Id,
                                    'updateDescription'=>'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done
                            // entry 2 : credit whatever cash is debited
                            // start reverse entry
                            $accountTransaction = AccountTransaction::where(['supplier_id'=> $purchased->supplier_id,])->get();
                            $last_closing=$accountTransaction->last()->Differentiate;
                            $description_string='FullCashPurchase|'.$Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_debited = $previous_entry->Debit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => 0.00,
                                    'Credit' => $previously_debited,
                                    'Differentiate' => $last_closing+$previously_debited,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'FullCashPurchase|'.$Id,
                                    'updateDescription'=>'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done

                            // reverse cash entry start
                            // reverse cash entry end
                            // no need to make cash entries because amount is same only supplier is changing
                            // make new cash entry for correct supplier start
                            // make new cash entry for correct supplier end
                        }

                        /*new entry*/
                        // start new entry for updated supplier with checking all three cases
                        $accountTransaction = AccountTransaction::where(['supplier_id'=> $request->Data['supplier_id'],])->get();
                        // totally credit
                        if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00) {
                            $totalCredit = $request->Data['grandTotal'];
                            $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                            $AccData =
                                [
                                    'supplier_id' => $request->Data['supplier_id'],
                                    'Credit' => $totalCredit,
                                    'Debit' => 0.00,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Purchase|'.$Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                        }
                        // partial payment some cash some credit
                        elseif($request->Data['paidBalance'] > 0 AND $request->Data['paidBalance'] < $request->Data['grandTotal'] )
                        {
                            $differenceValue = $accountTransaction->last()->Differentiate - $request->Data['paidBalance'];
                            $totalCredit = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                            $difference = $differenceValue + $request->Data['grandTotal'];

                            //make credit entry for the purchase
                            $AccData =
                                [
                                    'supplier_id' => $request->Data['supplier_id'],
                                    'Credit' => $request->Data['grandTotal'],
                                    'Debit' => 0.00,
                                    'Differentiate' => $totalCredit,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Purchase|'.$Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);

                            //make debit entry for the whatever cash is paid
                            $difference=$totalCredit-$request->Data['paidBalance'];
                            $AccData =
                                [
                                    'supplier_id' => $request->Data['supplier_id'],
                                    'Credit' => 0.00,
                                    'Debit' => $request->Data['paidBalance'],
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'PartialCashPurchase|'.$Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                        }
                        // fully paid with cash
                        else
                        {
                            $totalCredit = $request->Data['grandTotal'];
                            $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                            //make credit entry for the purchase
                            $AccountTransactions=AccountTransaction::Create([
                                'supplier_id' => $request->Data['supplier_id'],
                                'Credit' => $totalCredit,
                                'Debit' => 0.00,
                                'Differentiate' => $difference,
                                'createdDate' => $request->Data['PurchaseDate'],
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Purchase|'.$Id,
                            ]);

                            //make debit entry for the whatever cash is paid
                            $difference=$difference-$request->Data['paidBalance'];
                            $AccountTransactions=AccountTransaction::Create([
                                'supplier_id' => $request->Data['supplier_id'],
                                'Credit' => 0.00,
                                'Debit' => $request->Data['paidBalance'],
                                'Differentiate' => $difference,
                                'createdDate' => $request->Data['PurchaseDate'],
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'FullCashPurchase|'.$Id,
                            ]);
                        }
                        /*new entry*/
                    }

                    // check if only grand total is changed and not the supplier
                    elseif($request->Data['supplier_id']==$purchased->supplier_id  AND $purchased->grandTotal!=$request->Data['grandTotal'])
                    {
                        // 1 : reverse older entry
                        // start reverse entry
                        $last_closing=$accountTransaction->last()->Differentiate;
                        $description_string='Purchase|'.$Id;
                        $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                        $previously_credited = $previous_entry->Credit;
                        $AccData =
                            [
                                'supplier_id' => $purchased->supplier_id,
                                'Debit' => $previously_credited,
                                'Credit' => 0.00,
                                'Differentiate' => $last_closing-$previously_credited,
                                'createdDate' => $request->Data['PurchaseDate'],
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Purchase|'.$Id,
                                'updateDescription'=>'hide',
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // also hide previous entry start
                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                        // also hide previous entry end
                        // reverse entry done

                        /* new entry start */
                        // make new entry then check account balance
                        $accountTransaction = AccountTransaction::where(['supplier_id'=> $request->Data['supplier_id'],])->get();

                        $totalCredit = $request->Data['grandTotal'];
                        $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                        $AccData =
                            [
                                'supplier_id' => $request->Data['supplier_id'],
                                'Credit' => $totalCredit,
                                'Debit' => 0.00,
                                'Differentiate' => $difference,
                                'createdDate' => $request->Data['PurchaseDate'],
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Purchase|'.$Id,
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        $accountTransaction_ref=$AccountTransactions->id;
                        /* new entry end */

                        // if difference is positive meaning advance is over and we are payable
                        // so update purchase entry with difference amount as paid amount
                        if($difference<0)
                        {
                            // meaning after paying new amount there is still advance amount
                            $this_purchase = Purchase::find($purchased->id);
                            $this_purchase->update([
                                "paidBalance"        => $request->Data['grandTotal'],
                                "remainingBalance"   => 0.00,
                                "IsPaid" => 1,
                                "IsPartialPaid" => 0,
                                "IsNeedStampOrSignature" => false,
                                "Description" => 'AutoPaid',
                            ]);
                        }
                        elseif($difference>0)
                        {
                            // now we are payable so differance amount will be paid amount and make it partial paid
                            $this_purchase = Purchase::find($purchased->id);
                            $this_purchase->update([
                                "paidBalance"        => $request->Data['grandTotal']-$difference,
                                "remainingBalance"   => $difference,
                                "IsPaid" => 0,
                                "IsPartialPaid" => 1,
                                "IsNeedStampOrSignature" => false,
                                "Description" => 'AutoPaid',
                            ]);
                        }
                        // fully paid case will come here
                    }

                    // check both supplier and grandTotal is changed meaning case 3
                    elseif($request->Data['supplier_id']!=$purchased->supplier_id  AND $purchased->grandTotal!=$request->Data['grandTotal'])
                    {
                        //case : 1 full credit entry + payment is not done yet like isPaid=0 and IsPartialPaid=0
                        if($purchased->IsPaid==0 && $purchased->IsPartialPaid==0)
                        {
                            // start reverse entry
                            $last_closing=$accountTransaction->last()->Differentiate;
                            $description_string='Purchase|'.$Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_credited = $previous_entry->Credit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing-$previously_credited,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Purchase|'.$Id,
                                    'updateDescription'=>'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done
                        }
                        //case : 2 partial cash is paid and some amount is credit + payment is not fully done yet like isPaid=0 and IsPartialPaid=1
                        elseif($purchased->paidBalance > 0 AND $purchased->paidBalance < $purchased->grandTotal AND $purchased->IsPartialPaid==1)
                        {
                            // entry 1 : debit entry for purchase
                            // start reverse entry
                            $last_closing=$accountTransaction->last()->Differentiate;
                            $description_string='Purchase|'.$Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_credited = $previous_entry->Credit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing-$previously_credited,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Purchase|'.$Id,
                                    'updateDescription'=>'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done
                            // entry 2 : credit whatever cash is debited
                            // start reverse entry
                            $accountTransaction = AccountTransaction::where(['supplier_id'=> $purchased->supplier_id,])->get();
                            $last_closing=$accountTransaction->last()->Differentiate;
                            $description_string='PartialCashPurchase|'.$Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_debited = $previous_entry->Debit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => 0.00,
                                    'Credit' => $previously_debited,
                                    'Differentiate' => $last_closing+$previously_debited,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'PartialCashPurchase|'.$Id,
                                    'updateDescription'=>'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done

                            // reverse cash entry start
                            // reverse cash entry end
                            // no need to make cash entries because amount is same only supplier is changing
                            // make new cash entry for correct supplier start
                            // make new cash entry for correct supplier end
                        }
                        //case : 3 fully cash paid + isPaid=1 and IsPartialPaid=1
                        else
                        {
                            // entry 1 : debit entry for purchase
                            // start reverse entry
                            $last_closing=$accountTransaction->last()->Differentiate;
                            $description_string='Purchase|'.$Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_credited = $previous_entry->Credit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing-$previously_credited,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Purchase|'.$Id,
                                    'updateDescription'=>'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done

                            // entry 2 : credit whatever cash is debited
                            // start reverse entry
                            $accountTransaction = AccountTransaction::where(['supplier_id'=> $purchased->supplier_id,])->get();
                            $last_closing=$accountTransaction->last()->Differentiate;
                            $description_string='FullCashPurchase|'.$Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_debited = $previous_entry->Debit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => 0.00,
                                    'Credit' => $previously_debited,
                                    'Differentiate' => $last_closing+$previously_debited,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'FullCashPurchase|'.$Id,
                                    'updateDescription'=>'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done

                            // reverse cash entry start
                            // reverse cash entry end
                            // no need to make cash entries because amount is same only supplier is changing
                            // make new cash entry for correct supplier start
                            // make new cash entry for correct supplier end
                        }

                        /*new entry*/
                        // start new entry for updated supplier with checking all three cases
                        $accountTransaction = AccountTransaction::where(['supplier_id'=> $request->Data['supplier_id'],])->get();
                        // totally credit
                        if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00) {
                            $totalCredit = $request->Data['grandTotal'];
                            $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                            $AccData =
                                [
                                    'supplier_id' => $request->Data['supplier_id'],
                                    'Credit' => $totalCredit,
                                    'Debit' => 0.00,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Purchase|'.$Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                        }
                        // partial payment some cash some credit
                        elseif($request->Data['paidBalance'] > 0 AND $request->Data['paidBalance'] < $request->Data['grandTotal'] )
                        {
                            $differenceValue = $accountTransaction->last()->Differentiate - $request->Data['paidBalance'];
                            $totalCredit = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                            $difference = $differenceValue + $request->Data['grandTotal'];

                            //make credit entry for the purchase
                            $AccData =
                                [
                                    'supplier_id' => $request->Data['supplier_id'],
                                    'Credit' => $request->Data['grandTotal'],
                                    'Debit' => 0.00,
                                    'Differentiate' => $totalCredit,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Purchase|'.$Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);

                            //make debit entry for the whatever cash is paid
                            $difference=$totalCredit-$request->Data['paidBalance'];
                            $AccData =
                                [
                                    'supplier_id' => $request->Data['supplier_id'],
                                    'Credit' => 0.00,
                                    'Debit' => $request->Data['paidBalance'],
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'PartialCashPurchase|'.$Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                        }
                        // fully paid with cash
                        else
                        {
                            $totalCredit = $request->Data['grandTotal'];
                            $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                            //make credit entry for the purchase
                            $AccountTransactions=AccountTransaction::Create([
                                'supplier_id' => $request->Data['supplier_id'],
                                'Credit' => $totalCredit,
                                'Debit' => 0.00,
                                'Differentiate' => $difference,
                                'createdDate' => $request->Data['PurchaseDate'],
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Purchase|'.$Id,
                            ]);

                            //make debit entry for the whatever cash is paid
                            $difference=$difference-$request->Data['paidBalance'];
                            $AccountTransactions=AccountTransaction::Create([
                                'supplier_id' => $request->Data['supplier_id'],
                                'Credit' => 0.00,
                                'Debit' => $request->Data['paidBalance'],
                                'Differentiate' => $difference,
                                'createdDate' => $request->Data['PurchaseDate'],
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'FullCashPurchase|'.$Id,
                            ]);
                        }
                        /*new entry*/
                    }
                    //return Response()->json($accountTransaction);
                }
                //payment not done
                elseif($purchased->IsPaid==0 && $purchased->IsPartialPaid==0)
                {
                    if($request->Data['lastClosing']<0)
                    {
                        // if account closing is less then zero meaning it has some advance amount
                        // so deduct that amount and make related entry

                        //check if only supplier is changed and not quantity or price = grand total is same as previous
                        if($request->Data['supplier_id']!=$purchased->supplier_id  AND $purchased->grandTotal==$request->Data['grandTotal'])
                        {
                            //supplier is changed need to reverse all previously made account entries for the previous supplier

                            // start reverse entry for wrong supplier
                            $last_closing=$accountTransaction->last()->Differentiate;
                            $description_string='Purchase|'.$Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                            $previously_credited = $previous_entry->Credit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing-$previously_credited,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Purchase|'.$Id,
                                    'updateDescription'=>'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done for wrong supplier

                            /*new entry*/
                            // start new entry for right supplier and credit or debit account based on closing balance
                            $accountTransaction = AccountTransaction::where(['supplier_id'=> $request->Data['supplier_id'],])->get();
                            $totalCredit = $request->Data['grandTotal'];
                            $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                            $AccData =
                                [
                                    'supplier_id' => $request->Data['supplier_id'],
                                    'Credit' => $totalCredit,
                                    'Debit' => 0.00,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Purchase|'.$Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            if($difference<0)
                            {
                                // still there is advance amount so make it fully paid
                                $this_purchase = Purchase::find($purchased->id);
                                $this_purchase->update([
                                    "paidBalance"        => $request->Data['grandTotal'],
                                    "remainingBalance"   => 0.00,
                                    "IsPaid" => 1,
                                    "IsPartialPaid" => 0,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid',
                                ]);
                            }
                            elseif($difference>0)
                            {
                                if($difference==($request->Data['grandTotal']))
                                {
                                    // now we are payable so differance amount will be paid amount and make it partial paid
                                    $this_purchase = Purchase::find($purchased->id);
                                    $this_purchase->update([
                                        "paidBalance"        => 0,
                                        "remainingBalance"   => $difference,
                                        "IsPaid" => 0,
                                        "IsPartialPaid" => 0,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid',
                                    ]);
                                }
                                else
                                {
                                    // now we are payable so differance amount will be paid amount and make it partial paid
                                    $this_purchase = Purchase::find($purchased->id);
                                    $this_purchase->update([
                                        "paidBalance"        => $request->Data['grandTotal']-$difference,
                                        "remainingBalance"   => $difference,
                                        "IsPaid" => 0,
                                        "IsPartialPaid" => 1,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid',
                                    ]);
                                }
                            }
                            /*new entry*/
                        }

                        // check if only grand total is changed and not the supplier
                        elseif($request->Data['supplier_id']==$purchased->supplier_id  AND $purchased->grandTotal!=$request->Data['grandTotal'])
                        {
                            // 1 : reverse older entry
                            // start reverse entry
                            $last_closing=$accountTransaction->last()->Differentiate;
                            $description_string='Purchase|'.$Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                            $previously_credited = $previous_entry->Credit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing-$previously_credited,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Purchase|'.$Id,
                                    'updateDescription'=>'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done

                            /* new entry start */
                            // make new entry then check account balance
                            $accountTransaction = AccountTransaction::where(['supplier_id'=> $request->Data['supplier_id'],])->get();

                            $totalCredit = $request->Data['grandTotal'];
                            $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                            $AccData =
                                [
                                    'supplier_id' => $request->Data['supplier_id'],
                                    'Credit' => $totalCredit,
                                    'Debit' => 0.00,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Purchase|'.$Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            $accountTransaction_ref=$AccountTransactions->id;
                            /* new entry end */

                            // if difference is positive meaning advance is over and we are payable
                            // so update purchase entry with difference amount as paid amount
                            if($difference<0)
                            {
                                // still there is advance amount so make it fully paid
                                $this_purchase = Purchase::find($purchased->id);
                                $this_purchase->update([
                                    "paidBalance"        => $request->Data['grandTotal'],
                                    "remainingBalance"   => 0.00,
                                    "IsPaid" => 1,
                                    "IsPartialPaid" => 0,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid',
                                ]);
                            }
                            elseif($difference>0)
                            {
                                if($difference==($request->Data['grandTotal']))
                                {
                                    // now we are payable so differance amount will be paid amount and make it partial paid
                                    $this_purchase = Purchase::find($purchased->id);
                                    $this_purchase->update([
                                        "paidBalance"        => $request->Data['grandTotal'],
                                        "remainingBalance"   => $difference,
                                        "IsPaid" => 1,
                                        "IsPartialPaid" => 0,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid',
                                    ]);
                                }
                                else
                                {
                                    // now we are payable so differance amount will be paid amount and make it partial paid
                                    $this_purchase = Purchase::find($purchased->id);
                                    $this_purchase->update([
                                        "paidBalance"        => $request->Data['grandTotal']-$difference,
                                        "remainingBalance"   => $difference,
                                        "IsPaid" => 0,
                                        "IsPartialPaid" => 1,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid',
                                    ]);
                                }
                            }
                            // fully paid case will come here
                        }

                        // check both supplier and grandTotal is changed meaning case 3
                        elseif($request->Data['supplier_id']!=$purchased->supplier_id  AND $purchased->grandTotal!=$request->Data['grandTotal'])
                        {
                            // start reverse entry for wrong supplier with wrong entries
                            $last_closing=$accountTransaction->last()->Differentiate;
                            $description_string='Purchase|'.$Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                            $previously_credited = $previous_entry->Credit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing-$previously_credited,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Purchase|'.$Id,
                                    'updateDescription'=>'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done for wrong supplier

                            /*new entry with right grand total */
                            // start new entry for right supplier and credit or debit account based on closing balance
                            $accountTransaction = AccountTransaction::where(['supplier_id'=> $request->Data['supplier_id'],])->get();
                            $totalCredit = $request->Data['grandTotal'];
                            $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                            $AccData =
                                [
                                    'supplier_id' => $request->Data['supplier_id'],
                                    'Credit' => $totalCredit,
                                    'Debit' => 0.00,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Purchase|'.$Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            if($difference<0)
                            {
                                // still there is advance amount so make it fully paid
                                $this_purchase = Purchase::find($purchased->id);
                                $this_purchase->update([
                                    "paidBalance"        => $request->Data['grandTotal'],
                                    "remainingBalance"   => 0.00,
                                    "IsPaid" => 1,
                                    "IsPartialPaid" => 0,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid',
                                ]);
                            }
                            elseif($difference>0)
                            {
                                if($difference==($request->Data['grandTotal']))
                                {
                                    // now we are payable so differance amount will be paid amount and make it partial paid
                                    $this_purchase = Purchase::find($purchased->id);
                                    $this_purchase->update([
                                        "paidBalance"        => $request->Data['grandTotal'],
                                        "remainingBalance"   => $difference,
                                        "IsPaid" => 1,
                                        "IsPartialPaid" => 0,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid',
                                    ]);
                                }
                                else
                                {
                                    // now we are payable so differance amount will be paid amount and make it partial paid
                                    $this_purchase = Purchase::find($purchased->id);
                                    $this_purchase->update([
                                        "paidBalance"        => $request->Data['grandTotal']-$difference,
                                        "remainingBalance"   => $difference,
                                        "IsPaid" => 0,
                                        "IsPartialPaid" => 1,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid',
                                    ]);
                                }
                            }
                            /*new entry with right grand total*/
                        }
                    }
                    else
                    {
                        // if paid balance is not same as earlier need to update cash account as well
                        if($purchased->paidBalance!=$request->Data['paidBalance'])
                        {
                            //check if previously cash transaction done with this purchase id
                            $description_string='CashPurchase|'.$Id;
                            $previous_cash_entry = CashTransaction::get()->where('company_id','=',$company_id)->where('Details','like',$description_string)->last();
                            if($previous_cash_entry)
                            {
                                // start reverse entry
                                $previously_credited = $previous_cash_entry->Credit;
                                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                                $difference = $cashTransaction->last()->Differentiate;
                                $cash_transaction = new CashTransaction();
                                $cash_transaction->Reference=$Id;
                                $cash_transaction->createdDate=$request->Data['PurchaseDate'];
                                $cash_transaction->Type='purchases';
                                $cash_transaction->Details='CashPurchase|'.$Id.'hide';
                                $cash_transaction->Credit=0.00;
                                $cash_transaction->Debit=$previously_credited;
                                $cash_transaction->Differentiate=$difference+$previously_credited;
                                $cash_transaction->user_id = $user_id;
                                $cash_transaction->company_id = $company_id;
                                $cash_transaction->save();
                                // also hide previous entry start
                                CashTransaction::where('id', $previous_cash_entry->id)->update(array('Details' => 'CashPurchase|'.$Id.'hide'));
                                // also hide previous entry end
                                // reverse entry done

                                // start new entry
                                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                                $difference = $cashTransaction->last()->Differentiate;
                                $cash_transaction = new CashTransaction();
                                $cash_transaction->Reference=$Id;
                                $cash_transaction->createdDate=$request->Data['PurchaseDate'];
                                $cash_transaction->Type='purchases';
                                $cash_transaction->Details='CashPurchase|'.$Id;
                                $cash_transaction->Credit=$request->Data['paidBalance'];
                                $cash_transaction->Debit=0.00;
                                $cash_transaction->Differentiate=$difference-$request->Data['paidBalance'];
                                $cash_transaction->user_id = $user_id;
                                $cash_transaction->company_id = $company_id;
                                $cash_transaction->save();
                                // end new entry

                                // now here we check if only and only cash paid is updating and none of the below case will execute then we need..
                                // to check if there any existing entry with PartialCashPurchase|$id and not hidden we need to reverse that entry
                                if($request->Data['supplier_id']==$purchased->supplier_id  AND $purchased->grandTotal==$request->Data['grandTotal'])
                                {
                                    $description_string='PartialCashPurchase|'.$Id;
                                    $previous_entry = AccountTransaction::get()->where('company_id','=',$company_id)->where('Description','like',$description_string)->last();
                                    if($previous_entry)
                                    {
                                        // start revers entry
                                        $accountTransaction = AccountTransaction::where(['supplier_id'=> $purchased->supplier_id,])->get();
                                        $last_closing=$accountTransaction->last()->Differentiate;
                                        $previously_debited = $previous_entry->Debit;
                                        $AccData =
                                            [
                                                'supplier_id' => $purchased->supplier_id,
                                                'Debit' => 0.00,
                                                'Credit' => $previously_debited,
                                                'Differentiate' => $last_closing+$previously_debited,
                                                'createdDate' => $request->Data['PurchaseDate'],
                                                'user_id' => $user_id,
                                                'company_id' => $company_id,
                                                'Description'=>'PartialCashPurchase|'.$Id,
                                                'updateDescription'=>'hide',
                                            ];
                                        $AccountTransactions = AccountTransaction::Create($AccData);
                                        // also hide previous entry start
                                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                        // also hide previous entry end
                                        // reverse entry done

                                        // start new entry
                                        $accountTransaction = AccountTransaction::where(['supplier_id'=> $purchased->supplier_id,])->get();
                                        $last_closing=$accountTransaction->last()->Differentiate;
                                        $AccData =
                                            [
                                                'supplier_id' => $purchased->supplier_id,
                                                'Debit' => $request->Data['paidBalance'],
                                                'Credit' => 0.00,
                                                'Differentiate' => $last_closing-$request->Data['paidBalance'],
                                                'createdDate' => $request->Data['PurchaseDate'],
                                                'user_id' => $user_id,
                                                'company_id' => $company_id,
                                                'Description'=>'PartialCashPurchase|'.$Id,
                                            ];
                                        $AccountTransactions = AccountTransaction::Create($AccData);
                                        // new entry done
                                    }
                                }
                            }
                            else
                            {
                                // start new entry
                                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                                $difference = $cashTransaction->last()->Differentiate;
                                $cash_transaction = new CashTransaction();
                                $cash_transaction->Reference=$Id;
                                $cash_transaction->createdDate=$request->Data['PurchaseDate'];
                                $cash_transaction->Type='purchases';
                                $cash_transaction->Details='CashPurchase|'.$Id;
                                $cash_transaction->Credit=$request->Data['paidBalance'];
                                $cash_transaction->Debit=0.00;
                                $cash_transaction->Differentiate=$difference-$request->Data['paidBalance'];
                                $cash_transaction->user_id = $user_id;
                                $cash_transaction->company_id = $company_id;
                                $cash_transaction->save();
                                // end new entry

                                // now here we check if only and only cash paid is updating and none of the below case will execute then we need..
                                // to create one more cash entry for this purchase as partial cash purchase entry in account transaction
                                if($request->Data['supplier_id']==$purchased->supplier_id  AND $purchased->grandTotal==$request->Data['grandTotal'])
                                {
                                    $accountTransaction = AccountTransaction::where(['supplier_id'=> $purchased->supplier_id,])->get();
                                    $last_closing=$accountTransaction->last()->Differentiate;
                                    $description_string='Purchase|'.$Id;
                                    $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                                    //echo "<pre>";print_r($previous_entry->Credit);die;
                                    $previously_debited = $previous_entry->Debit;
                                    $AccData =
                                        [
                                            'supplier_id' => $purchased->supplier_id,
                                            'Debit' => $request->Data['paidBalance'],
                                            'Credit' => 0.00,
                                            'Differentiate' => $last_closing-$request->Data['paidBalance'],
                                            'createdDate' => $request->Data['PurchaseDate'],
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description'=>'PartialCashPurchase|'.$Id,
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);
                                }
                            }
                        }

                        // here will come 3 cases
                        // 1. only supplier is updating - quantity and price remains same
                        // 2. only quantity or price updating - supplier is the same
                        // 3. both supplier and quantity or price updating

                        //check if only supplier is changed and not quantity or price = grand total is same as previous
                        if($request->Data['supplier_id']!=$purchased->supplier_id  AND $purchased->grandTotal==$request->Data['grandTotal'])
                        {
                            //supplier is changed need to reverse all previously made account entries for the previous supplier

                            //case : 1 full credit entry + payment is not done yet like isPaid=0 and IsPartialPaid=0
                            if($purchased->IsPaid==0 && $purchased->IsPartialPaid==0)
                            {
                                // start reverse entry
                                $last_closing=$accountTransaction->last()->Differentiate;
                                $description_string='Purchase|'.$Id;
                                $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_credited = $previous_entry->Credit;
                                $AccData =
                                    [
                                        'supplier_id' => $purchased->supplier_id,
                                        'Debit' => $previously_credited,
                                        'Credit' => 0.00,
                                        'Differentiate' => $last_closing-$previously_credited,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Purchase|'.$Id,
                                        'updateDescription'=>'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done
                            }
                            //case : 2 partial cash is paid and some amount is credit + payment is not fully done yet like isPaid=0 and IsPartialPaid=1
                            elseif($purchased->paidBalance > 0 AND $purchased->paidBalance < $purchased->grandTotal AND $purchased->IsPartialPaid==1)
                            {
                                // entry 1 : debit entry for purchase
                                // start reverse entry
                                $last_closing=$accountTransaction->last()->Differentiate;
                                $description_string='Purchase|'.$Id;
                                $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_credited = $previous_entry->Credit;
                                $AccData =
                                    [
                                        'supplier_id' => $purchased->supplier_id,
                                        'Debit' => $previously_credited,
                                        'Credit' => 0.00,
                                        'Differentiate' => $last_closing-$previously_credited,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Purchase|'.$Id,
                                        'updateDescription'=>'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done
                                // entry 2 : credit whatever cash is debited
                                // start reverse entry
                                $accountTransaction = AccountTransaction::where(['supplier_id'=> $purchased->supplier_id,])->get();
                                $last_closing=$accountTransaction->last()->Differentiate;
                                $description_string='PartialCashPurchase|'.$Id;
                                $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_debited = $previous_entry->Debit;
                                $AccData =
                                    [
                                        'supplier_id' => $purchased->supplier_id,
                                        'Debit' => 0.00,
                                        'Credit' => $previously_debited,
                                        'Differentiate' => $last_closing+$previously_debited,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'PartialCashPurchase|'.$Id,
                                        'updateDescription'=>'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done

                                // reverse cash entry start
                                // reverse cash entry end
                                // no need to make cash entries because amount is same only supplier is changing
                                // make new cash entry for correct supplier start
                                // make new cash entry for correct supplier end
                            }
                            //case : 3 fully cash paid + isPaid=1 and IsPartialPaid=1
                            else
                            {
                                // entry 1 : debit entry for purchase
                                // start reverse entry
                                $last_closing=$accountTransaction->last()->Differentiate;
                                $description_string='Purchase|'.$Id;
                                $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_credited = $previous_entry->Credit;
                                $AccData =
                                    [
                                        'supplier_id' => $purchased->supplier_id,
                                        'Debit' => $previously_credited,
                                        'Credit' => 0.00,
                                        'Differentiate' => $last_closing-$previously_credited,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Purchase|'.$Id,
                                        'updateDescription'=>'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done
                                // entry 2 : credit whatever cash is debited
                                // start reverse entry
                                $accountTransaction = AccountTransaction::where(['supplier_id'=> $purchased->supplier_id,])->get();
                                $last_closing=$accountTransaction->last()->Differentiate;
                                $description_string='FullCashPurchase|'.$Id;
                                $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_debited = $previous_entry->Debit;
                                $AccData =
                                    [
                                        'supplier_id' => $purchased->supplier_id,
                                        'Debit' => 0.00,
                                        'Credit' => $previously_debited,
                                        'Differentiate' => $last_closing+$previously_debited,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'FullCashPurchase|'.$Id,
                                        'updateDescription'=>'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done

                                // reverse cash entry start
                                // reverse cash entry end
                                // no need to make cash entries because amount is same only supplier is changing
                                // make new cash entry for correct supplier start
                                // make new cash entry for correct supplier end
                            }

                            /*new entry*/
                            // start new entry for updated supplier with checking all three cases
                            $accountTransaction = AccountTransaction::where(['supplier_id'=> $request->Data['supplier_id'],])->get();
                            // totally credit
                            if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00) {
                                $totalCredit = $request->Data['grandTotal'];
                                $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                                $AccData =
                                    [
                                        'supplier_id' => $request->Data['supplier_id'],
                                        'Credit' => $totalCredit,
                                        'Debit' => 0.00,
                                        'Differentiate' => $difference,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Purchase|'.$Id,
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                            }
                            // partial payment some cash some credit
                            elseif($request->Data['paidBalance'] > 0 AND $request->Data['paidBalance'] < $request->Data['grandTotal'] )
                            {
                                $differenceValue = $accountTransaction->last()->Differentiate - $request->Data['paidBalance'];
                                $totalCredit = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                                $difference = $differenceValue + $request->Data['grandTotal'];

                                //make credit entry for the purchase
                                $AccData =
                                    [
                                        'supplier_id' => $request->Data['supplier_id'],
                                        'Credit' => $request->Data['grandTotal'],
                                        'Debit' => 0.00,
                                        'Differentiate' => $totalCredit,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Purchase|'.$Id,
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);

                                //make debit entry for the whatever cash is paid
                                $difference=$totalCredit-$request->Data['paidBalance'];
                                $AccData =
                                    [
                                        'supplier_id' => $request->Data['supplier_id'],
                                        'Credit' => 0.00,
                                        'Debit' => $request->Data['paidBalance'],
                                        'Differentiate' => $difference,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'PartialCashPurchase|'.$Id,
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                            }
                            // fully paid with cash
                            else
                            {
                                $totalCredit = $request->Data['grandTotal'];
                                $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                                //make credit entry for the purchase
                                $AccountTransactions=AccountTransaction::Create([
                                    'supplier_id' => $request->Data['supplier_id'],
                                    'Credit' => $totalCredit,
                                    'Debit' => 0.00,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Purchase|'.$Id,
                                ]);

                                //make debit entry for the whatever cash is paid
                                $difference=$difference-$request->Data['paidBalance'];
                                $AccountTransactions=AccountTransaction::Create([
                                    'supplier_id' => $request->Data['supplier_id'],
                                    'Credit' => 0.00,
                                    'Debit' => $request->Data['paidBalance'],
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'FullCashPurchase|'.$Id,
                                ]);
                            }
                            /*new entry*/
                        }
                        // check if only grand total is changed and not the supplier
                        elseif($request->Data['supplier_id']==$purchased->supplier_id  AND $purchased->grandTotal!=$request->Data['grandTotal'])
                        {
                            //supplier is not changed then need to find what is the differance in total and for payment changes
                            // here in two way we can proceed
                            // option 1 : reverse previous account entries and make new entry
                            // option 2 : find out plus minus differance and make one another entry with differences
                            // option 2 is not preferable because of while displaying we need to add or subtract similar purchase id entry so that is little tricky in query
                            // also need to manage isPaid and isPartialPaid flag according

                            // implementation of option 2
                            //case : 1 full credit entry + payment is not done yet like isPaid=0 and IsPartialPaid=0
                            if($purchased->IsPaid==0 && $purchased->IsPartialPaid==0)
                            {
                                // start reverse entry
                                $last_closing=$accountTransaction->last()->Differentiate;
                                $description_string='Purchase|'.$Id;
                                $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_credited = $previous_entry->Credit;
                                $AccData =
                                    [
                                        'supplier_id' => $purchased->supplier_id,
                                        'Debit' => $previously_credited,
                                        'Credit' => 0.00,
                                        'Differentiate' => $last_closing-$previously_credited,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Purchase|'.$Id,
                                        'updateDescription'=>'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done
                            }
                            //case : 2 partial cash is paid and some amount is credit + payment is not fully done yet like isPaid=0 and IsPartialPaid=1
                            elseif($purchased->paidBalance > 0 AND $purchased->paidBalance < $purchased->grandTotal AND $purchased->IsPartialPaid==1)
                            {
                                // entry 1 : debit entry for purchase
                                // start reverse entry
                                $last_closing=$accountTransaction->last()->Differentiate;
                                $description_string='Purchase|'.$Id;
                                $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_credited = $previous_entry->Credit;
                                $AccData =
                                    [
                                        'supplier_id' => $purchased->supplier_id,
                                        'Debit' => $previously_credited,
                                        'Credit' => 0.00,
                                        'Differentiate' => $last_closing-$previously_credited,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Purchase|'.$Id,
                                        'updateDescription'=>'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done
                                // entry 2 : credit whatever cash is debited
                                // start reverse entry
                                $accountTransaction = AccountTransaction::where(['supplier_id'=> $purchased->supplier_id,])->get();
                                $last_closing=$accountTransaction->last()->Differentiate;
                                $description_string='PartialCashPurchase|'.$Id;
                                $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_debited = $previous_entry->Debit;
                                $AccData =
                                    [
                                        'supplier_id' => $purchased->supplier_id,
                                        'Debit' => 0.00,
                                        'Credit' => $previously_debited,
                                        'Differentiate' => $last_closing+$previously_debited,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'PartialCashPurchase|'.$Id,
                                        'updateDescription'=>'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done

                                // reverse cash entry start
                                // reverse cash entry end
                                // no need to make cash entries because amount is same only supplier is changing
                                // make new cash entry for correct supplier start
                                // make new cash entry for correct supplier end
                            }
                            //case : 3 fully cash paid + isPaid=1 and IsPartialPaid=1
                            else
                            {
                                // entry 1 : debit entry for purchase
                                // start reverse entry
                                $last_closing=$accountTransaction->last()->Differentiate;
                                $description_string='Purchase|'.$Id;
                                $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_credited = $previous_entry->Credit;
                                $AccData =
                                    [
                                        'supplier_id' => $purchased->supplier_id,
                                        'Debit' => $previously_credited,
                                        'Credit' => 0.00,
                                        'Differentiate' => $last_closing-$previously_credited,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Purchase|'.$Id,
                                        'updateDescription'=>'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done
                                // entry 2 : credit whatever cash is debited
                                // start reverse entry
                                $accountTransaction = AccountTransaction::where(['supplier_id'=> $purchased->supplier_id,])->get();
                                $last_closing=$accountTransaction->last()->Differentiate;
                                $description_string='FullCashPurchase|'.$Id;
                                $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_debited = $previous_entry->Debit;
                                $AccData =
                                    [
                                        'supplier_id' => $purchased->supplier_id,
                                        'Debit' => 0.00,
                                        'Credit' => $previously_debited,
                                        'Differentiate' => $last_closing+$previously_debited,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'FullCashPurchase|'.$Id,
                                        'updateDescription'=>'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done

                                // reverse cash entry start
                                // reverse cash entry end
                                // no need to make cash entries because amount is same only supplier is changing
                                // make new cash entry for correct supplier start
                                // make new cash entry for correct supplier end
                            }

                            /* new entry */
                            // start new entry for updated supplier with checking all three cases
                            $accountTransaction = AccountTransaction::where(['supplier_id'=> $request->Data['supplier_id'],])->get();
                            // totally credit
                            if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00) {
                                $totalCredit = $request->Data['grandTotal'];
                                $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                                $AccData =
                                    [
                                        'supplier_id' => $request->Data['supplier_id'],
                                        'Credit' => $totalCredit,
                                        'Debit' => 0.00,
                                        'Differentiate' => $difference,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Purchase|'.$Id,
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                            }
                            // partial payment some cash some credit
                            elseif($request->Data['paidBalance'] > 0 AND $request->Data['paidBalance'] < $request->Data['grandTotal'] )
                            {
                                $differenceValue = $accountTransaction->last()->Differentiate - $request->Data['paidBalance'];
                                $totalCredit = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                                $difference = $differenceValue + $request->Data['grandTotal'];

                                //make credit entry for the purchase
                                $AccData =
                                    [
                                        'supplier_id' => $request->Data['supplier_id'],
                                        'Credit' => $request->Data['grandTotal'],
                                        'Debit' => 0.00,
                                        'Differentiate' => $totalCredit,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Purchase|'.$Id,
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);

                                //make debit entry for the whatever cash is paid
                                $difference=$totalCredit-$request->Data['paidBalance'];
                                $AccData =
                                    [
                                        'supplier_id' => $request->Data['supplier_id'],
                                        'Credit' => 0.00,
                                        'Debit' => $request->Data['paidBalance'],
                                        'Differentiate' => $difference,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'PartialCashPurchase|'.$Id,
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                            }
                            // fully paid with cash
                            else
                            {
                                $totalCredit = $request->Data['grandTotal'];
                                $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                                //make credit entry for the purchase
                                $AccountTransactions=AccountTransaction::Create([
                                    'supplier_id' => $request->Data['supplier_id'],
                                    'Credit' => $totalCredit,
                                    'Debit' => 0.00,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Purchase|'.$Id,
                                ]);

                                //make debit entry for the whatever cash is paid
                                $difference=$difference-$request->Data['paidBalance'];
                                $AccountTransactions=AccountTransaction::Create([
                                    'supplier_id' => $request->Data['supplier_id'],
                                    'Credit' => 0.00,
                                    'Debit' => $request->Data['paidBalance'],
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'FullCashPurchase|'.$Id,
                                ]);
                            }
                            /* new entry */
                        }
                        // check both supplier and grandTotal is changed meaning case 3
                        elseif($request->Data['supplier_id']!=$purchased->supplier_id  AND $purchased->grandTotal!=$request->Data['grandTotal'])
                        {
                            //case : 1 full credit entry + payment is not done yet like isPaid=0 and IsPartialPaid=0
                            if($purchased->IsPaid==0 && $purchased->IsPartialPaid==0)
                            {
                                // start reverse entry
                                $last_closing=$accountTransaction->last()->Differentiate;
                                $description_string='Purchase|'.$Id;
                                $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_credited = $previous_entry->Credit;
                                $AccData =
                                    [
                                        'supplier_id' => $purchased->supplier_id,
                                        'Debit' => $previously_credited,
                                        'Credit' => 0.00,
                                        'Differentiate' => $last_closing-$previously_credited,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Purchase|'.$Id,
                                        'updateDescription'=>'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done
                            }
                            //case : 2 partial cash is paid and some amount is credit + payment is not fully done yet like isPaid=0 and IsPartialPaid=1
                            elseif($purchased->paidBalance > 0 AND $purchased->paidBalance < $purchased->grandTotal AND $purchased->IsPartialPaid==1)
                            {
                                // entry 1 : debit entry for purchase
                                // start reverse entry
                                $last_closing=$accountTransaction->last()->Differentiate;
                                $description_string='Purchase|'.$Id;
                                $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_credited = $previous_entry->Credit;
                                $AccData =
                                    [
                                        'supplier_id' => $purchased->supplier_id,
                                        'Debit' => $previously_credited,
                                        'Credit' => 0.00,
                                        'Differentiate' => $last_closing-$previously_credited,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Purchase|'.$Id,
                                        'updateDescription'=>'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done
                                // entry 2 : credit whatever cash is debited
                                // start reverse entry
                                $accountTransaction = AccountTransaction::where(['supplier_id'=> $purchased->supplier_id,])->get();
                                $last_closing=$accountTransaction->last()->Differentiate;
                                $description_string='PartialCashPurchase|'.$Id;
                                $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_debited = $previous_entry->Debit;
                                $AccData =
                                    [
                                        'supplier_id' => $purchased->supplier_id,
                                        'Debit' => 0.00,
                                        'Credit' => $previously_debited,
                                        'Differentiate' => $last_closing+$previously_debited,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'PartialCashPurchase|'.$Id,
                                        'updateDescription'=>'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done

                                // reverse cash entry start
                                // reverse cash entry end
                                // no need to make cash entries because amount is same only supplier is changing
                                // make new cash entry for correct supplier start
                                // make new cash entry for correct supplier end
                            }
                            //case : 3 fully cash paid + isPaid=1 and IsPartialPaid=1
                            else
                            {
                                // entry 1 : debit entry for purchase
                                // start reverse entry
                                $last_closing=$accountTransaction->last()->Differentiate;
                                $description_string='Purchase|'.$Id;
                                $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_credited = $previous_entry->Credit;
                                $AccData =
                                    [
                                        'supplier_id' => $purchased->supplier_id,
                                        'Debit' => $previously_credited,
                                        'Credit' => 0.00,
                                        'Differentiate' => $last_closing-$previously_credited,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Purchase|'.$Id,
                                        'updateDescription'=>'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done

                                // entry 2 : credit whatever cash is debited
                                // start reverse entry
                                $accountTransaction = AccountTransaction::where(['supplier_id'=> $purchased->supplier_id,])->get();
                                $last_closing=$accountTransaction->last()->Differentiate;
                                $description_string='FullCashPurchase|'.$Id;
                                $previous_entry = AccountTransaction::get()->where('supplier_id','=',$purchased->supplier_id)->where('Description','like',$description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_debited = $previous_entry->Debit;
                                $AccData =
                                    [
                                        'supplier_id' => $purchased->supplier_id,
                                        'Debit' => 0.00,
                                        'Credit' => $previously_debited,
                                        'Differentiate' => $last_closing+$previously_debited,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'FullCashPurchase|'.$Id,
                                        'updateDescription'=>'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done

                                // reverse cash entry start
                                // reverse cash entry end
                                // no need to make cash entries because amount is same only supplier is changing
                                // make new cash entry for correct supplier start
                                // make new cash entry for correct supplier end
                            }

                            /*new entry*/
                            // start new entry for updated supplier with checking all three cases
                            $accountTransaction = AccountTransaction::where(['supplier_id'=> $request->Data['supplier_id'],])->get();
                            // totally credit
                            if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00) {
                                $totalCredit = $request->Data['grandTotal'];
                                $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                                $AccData =
                                    [
                                        'supplier_id' => $request->Data['supplier_id'],
                                        'Credit' => $totalCredit,
                                        'Debit' => 0.00,
                                        'Differentiate' => $difference,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Purchase|'.$Id,
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                            }
                            // partial payment some cash some credit
                            elseif($request->Data['paidBalance'] > 0 AND $request->Data['paidBalance'] < $request->Data['grandTotal'] )
                            {
                                $differenceValue = $accountTransaction->last()->Differentiate - $request->Data['paidBalance'];
                                $totalCredit = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                                $difference = $differenceValue + $request->Data['grandTotal'];

                                //make credit entry for the purchase
                                $AccData =
                                    [
                                        'supplier_id' => $request->Data['supplier_id'],
                                        'Credit' => $request->Data['grandTotal'],
                                        'Debit' => 0.00,
                                        'Differentiate' => $totalCredit,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Purchase|'.$Id,
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);

                                //make debit entry for the whatever cash is paid
                                $difference=$totalCredit-$request->Data['paidBalance'];
                                $AccData =
                                    [
                                        'supplier_id' => $request->Data['supplier_id'],
                                        'Credit' => 0.00,
                                        'Debit' => $request->Data['paidBalance'],
                                        'Differentiate' => $difference,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'PartialCashPurchase|'.$Id,
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                            }
                            // fully paid with cash
                            else
                            {
                                $totalCredit = $request->Data['grandTotal'];
                                $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                                //make credit entry for the purchase
                                $AccountTransactions=AccountTransaction::Create([
                                    'supplier_id' => $request->Data['supplier_id'],
                                    'Credit' => $totalCredit,
                                    'Debit' => 0.00,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Purchase|'.$Id,
                                ]);

                                //make debit entry for the whatever cash is paid
                                $difference=$difference-$request->Data['paidBalance'];
                                $AccountTransactions=AccountTransaction::Create([
                                    'supplier_id' => $request->Data['supplier_id'],
                                    'Credit' => 0.00,
                                    'Debit' => $request->Data['paidBalance'],
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'FullCashPurchase|'.$Id,
                                ]);
                            }
                            /*new entry*/
                        }
                        //return Response()->json($accountTransaction);
                    }
                }
            }
            ////////////////// end of account section gautam ////////////////

//            ////////////////// account section ////////////////
//            $accountTransaction = AccountTransaction::where(
//                [
//                    'supplier_id'=> $request->Data['supplier_id'],
//                ])->get();
//            if (!is_null($accountTransaction)) {
//                $lastAccountTransection = $accountTransaction->Last();
//                if ($lastAccountTransection->supplier_id != $purchased->supplier_id)
//                {
//                    if ($purchased->paidBalance == 0 || $purchased->paidBalance == 0.00) {
//                        $OldValue1 = $purchased->supplier->account_transaction->Last()->Debit - $purchased->grandTotal;
//                        $OldTotalDebit = $OldValue1;
//                        $OldTotalCredit = $purchased->supplier->account_transaction->Last()->Credit;
//                        $OldValue = $purchased->supplier->account_transaction->Last()->Differentiate + $purchased->grandTotal;
//                        $OldDifference = $OldValue;
//                    }
//                    elseif ($purchased->paidBalance > 0 AND $purchased->paidBalance < $purchased->grandTotal)
//                    {
//                        $OldTotalCredit = $purchased->supplier->account_transaction->Last()->Credit - $purchased->paidBalance;
//                        $OldTotalDebit = $purchased->supplier->account_transaction->Last()->Debit - $purchased->grandTotal;
//                        $differenceValue = $purchased->supplier->account_transaction->Last()->Differentiate - $purchased->paidBalance;
//                        $OldDifference = $differenceValue + $purchased->grandTotal;
//                    }
//                    else{
//                        $OldValue1 = $purchased->supplier->account_transaction->Last()->Credit - $purchased->paidBalance;
//                        $OldTotalCredit = $OldValue1;
//                        $OldTotalDebit = $purchased->supplier->account_transaction->Last()->Debit;
//                        $OldValue = $purchased->supplier->account_transaction->Last()->Differentiate - $purchased->paidBalance;
//                        $OldDifference = $OldValue;
//                    }
//                    $OldAccData =
//                        [
//                            'supplier_id' => $purchased->supplier_id,
//                            'Debit' => $OldTotalDebit,
//                            'Credit' => $OldTotalCredit,
//                            'Differentiate' => $OldDifference,
//                            'createdDate' => $purchased->supplier->account_transaction->Last()->createdDate,
//                            'user_id' =>$user_id,
//                            'company_id' => $company_id,
//                        ];
//                    $AccountTransactions = AccountTransaction::updateOrCreate([
//                        'id'   => $purchased->supplier->account_transaction->Last()->id,
//                    ], $OldAccData);
//
//                    if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00) {
//                        $totalDebit = $lastAccountTransection->Debit + $request->Data['grandTotal'];
//                        $totalCredit = $lastAccountTransection->Credit;
//                        $difference = $lastAccountTransection->Differentiate - $request->Data['grandTotal'];
//                    }
//                    elseif ($request->Data['paidBalance'] > 0 AND $request->Data['paidBalance'] < $request->Data['grandTotal'])
//                    {
//                        $totalDebit = $lastAccountTransection->Debit - $request->Data['paidBalance'];
//                        $totalCredit = $lastAccountTransection->Credit - $request->Data['grandTotal'];
//                        $differenceValue = $accountTransaction->last()->Differentiate - $request->Data['paidBalance'];
//                        $difference = $differenceValue + $request->Data['grandTotal'];
//                    }
//                    else{
//                        $totalCredit = $lastAccountTransection->Credit + $request->Data['paidBalance'];
//                        $totalDebit = $lastAccountTransection->Debit;
//                        $difference = $lastAccountTransection->Differentiate + $request->Data['paidBalance'];
//                    }
//                }
//                else
//                {
//                    if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00 || $request->Data['paidBalance'] == "") {
//                        if ($lastAccountTransection->createdDate != $purchased->supplier->account_transaction->last()->createdDate) {
//                            $totalDebit = $request->Data['grandTotal'];
//                        } else {
//                            $value1 = $lastAccountTransection->Debit - $purchased->grandTotal;
//                            $totalDebit = $value1 + $request->Data['grandTotal'];
//                        }
//                        $totalCredit = $lastAccountTransection->Credit;
//                        $value = $lastAccountTransection->Differentiate + $purchased->grandTotal;
//                        $difference = $value - $request->Data['grandTotal'];
////                                        return Response()->json($difference);
//                    }
//                    elseif ($request->Data['paidBalance'] > 0 AND $request->Data['paidBalance'] < $request->Data['grandTotal'])
//                    {
//
//                        if ($lastAccountTransection->createdDate != $purchased->supplier->account_transaction->last()->createdDate) {
//                            $totalCredit = $request->Data['paidBalance'];
//                            $totalDebit = $request->Data['grandTotal'];
//                        } else {
//                            $value1 = $lastAccountTransection->Credit - $purchased->paidBalance;
//                            $totalCredit = $value1 + $request->Data['paidBalance'];
//                            $valueC = $lastAccountTransection->Debit - $purchased->grandTotal;
//                            $totalDebit = $valueC + $request->Data['grandTotal'];
//                        }
//                        $differenceValue = $lastAccountTransection->Differentiate - $request->Data['paidBalance'];
//                        $difference = $differenceValue + $request->Data['grandTotal'];
//                    }
//                    else{
//                        if ($lastAccountTransection->createdDate != $purchased->supplier->account_transaction->last()->createdDate) {
//                            $totalCredit = $request->Data['paidBalance'];
//                        } else {
//                            $value1 = $lastAccountTransection->Credit - $purchased->paidBalance;
//                            $totalCredit = $value1 + $request->Data['paidBalance'];
//                        }
//                        $totalDebit = $lastAccountTransection->Debit;
//                        $value = $lastAccountTransection->Differentiate - $purchased->paidBalance;
//                        $difference = $value + $request->Data['paidBalance'];
//                    }
//                }
//
//                $AccData =
//                    [
//                        'supplier_id' => $request->Data['supplier_id'],
//                        'Credit' => $totalCredit,
//                        'Debit' => $totalDebit,
//                        'Differentiate' => $difference,
//                        'createdDate' => $lastAccountTransection->createdDate,
//                        'user_id' =>$user_id,
//                        'company_id' => $company_id,
//                    ];
//                $AccountTransactions = AccountTransaction::updateOrCreate([
//                    'createdDate'   => $lastAccountTransection->createdDate,
//                    'id'   => $lastAccountTransection->id,
//                ], $AccData);
//                //return Response()->json($accountTransaction);
//            }
//            ////////////////// end of account section ////////////////

            if($purchased->IsPaid==1 && $purchased->IsPartialPaid==0)
            {
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
                        //'paidBalance' => $request->Data['paidBalance'],
                        //'remainingBalance' => $request->Data['remainingBalance'],
                        'supplier_id' => $request->Data['supplier_id'],
                        'supplierNote' => $request->Data['supplierNote'],
                        //'IsPaid' => $isPaid,
                        //'IsPartialPaid' => $partialPaid,
                        //'IsNeedStampOrSignature' => false,
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
                        "unit_id"        => $detail['unit_id'],
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
                        "createdDate" => $request->Data['PurchaseDate'],
                        "supplier_id" => $request->Data['supplier_id'],
                    ]);
                }
                $ss = PurchaseDetail::where('purchase_id', array($purchaseDetails['purchase_id']))->get();
                return Response()->json($ss);
            }
            elseif($purchased->IsPaid==0 && $purchased->IsPartialPaid==1) {
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
                        //'paidBalance' => $request->Data['paidBalance'],
                        //'remainingBalance' => $request->Data['remainingBalance'],
                        'supplier_id' => $request->Data['supplier_id'],
                        'supplierNote' => $request->Data['supplierNote'],
                        //'IsPaid' => $isPaid,
                        //'IsPartialPaid' => $partialPaid,
                        //'IsNeedStampOrSignature' => false,
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
                        "unit_id"        => $detail['unit_id'],
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
                        "createdDate" => $request->Data['PurchaseDate'],
                        "supplier_id" => $request->Data['supplier_id'],
                    ]);
                }
                $ss = PurchaseDetail::where('purchase_id', array($purchaseDetails['purchase_id']))->get();
                return Response()->json($ss);
            }
            else
            {
                if($request->Data['lastClosing']<0)
                {
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
                            //'paidBalance' => $request->Data['paidBalance'],
                            //'remainingBalance' => $request->Data['remainingBalance'],
                            'supplier_id' => $request->Data['supplier_id'],
                            'supplierNote' => $request->Data['supplierNote'],
                            //'IsPaid' => $isPaid,
                            //'IsPartialPaid' => $partialPaid,
                            //'IsNeedStampOrSignature' => false,
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
                            "unit_id"        => $detail['unit_id'],
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
                            "createdDate" => $request->Data['PurchaseDate'],
                            "supplier_id" => $request->Data['supplier_id'],
                        ]);
                    }
                    $ss = PurchaseDetail::where('purchase_id', array($purchaseDetails['purchase_id']))->get();
                    return Response()->json($ss);
                }
                else
                {
                    if ($request->Data['paidBalance'] == 0.00 || $request->Data['paidBalance'] == 0) {
                        $isPaid = false;
                        $partialPaid =false;
                    }
                    elseif($request->Data['paidBalance'] >= $request->Data['grandTotal'])
                    {
                        $isPaid = true;
                        $partialPaid =false;
                    }
                    else
                    {
                        $isPaid = false;
                        $partialPaid =true;
                    }
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
                            'remainingBalance' => $request->Data['grandTotal'],
                            'supplier_id' => $request->Data['supplier_id'],
                            'supplierNote' => $request->Data['supplierNote'],
                            'IsPaid' => $isPaid,
                            'IsPartialPaid' => $partialPaid,
                            'IsNeedStampOrSignature' => false,
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
                            "unit_id"        => $detail['unit_id'],
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
                            "createdDate" => $request->Data['PurchaseDate'],
                            "supplier_id" => $request->Data['supplier_id'],
                        ]);
                    }
                    $ss = PurchaseDetail::where('purchase_id', array($purchaseDetails['purchase_id']))->get();
                    return Response()->json($ss);
                }
            }
        }
    }

    public function getById($Id)
    {
        $Response = PurchaseResource::collection(Purchase::where('id',$Id)->with('user','supplier','purchase_details','update_notes','documents')->get());
        $data = json_decode(json_encode($Response), true);
        return $data[0];
    }

    public function edit($Id)
    {
        $update_notes = UpdateNote::with('company','user')->where(['RelationId' => $Id, 'RelationTable' => 'purchases'])->get();
        //dd($update_notes[0]->Description);
        $suppliers = Supplier::all();
        $products = Product::all();
        $units = Unit::all();
        $purchase_details = PurchaseDetail::withTrashed()->with('purchase.supplier','user','product','unit')->where('purchase_id', $Id)->get();
        return view('admin.purchase.edit',compact('purchase_details','suppliers','products','update_notes','units'));
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

    public function PadNumber()
    {
//        $PadNumber = new PurchaseDetail();
//        $lastPad = $PadNumber->orderByDesc('PadNumber')->pluck('PadNumber')->first();
//        $newPad = ($lastPad + 1);
//        return $newPad;

//        $PadNumber = new PurchaseDetail();
//        $lastPad = $PadNumber->where('company_id',session('company_id'))->orderByDesc('PadNumber')->pluck('PadNumber')->first();
//        $newPad = ($lastPad + 1);

        // pad number according to max sales id
        $max_purchase_id = PurchaseDetail::where('company_id',session('company_id'))->find(DB::table('purchase_details')->max('id'));
        $lastPad = $max_purchase_id->PadNumber;
        if(!is_numeric($lastPad))
        {
            $newPad=1;
        }
        else
        {
            $newPad = ($lastPad + 1);
        }

        return $newPad;
    }

    public function print($id)
    {
        $data=$this->getById($id);
        //echo "<pre>";print_r($data);die;
        if(!empty($data['purchase_details']))
        {
            $company_title='WATAN PHARMA LLP.';
            $company_address='MUSSAFAH M13,PLOT 100, ABU DHABI,UAE';
            $company_email='Email : info@alhamood.ae';
            $company_mobile='Mobile : +971-25550870  +971-557383866  +971-569777861';
            $pdf = new PDF();
            $pdf::setPrintHeader(false);
            $pdf::setPrintFooter(false);
            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            $pdf::AddPage();$pdf::SetFont('times', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetXY(25,7);
            $pdf::SetFont('times', '', 12);
            $pdf::MultiCell(83, 5, $company_title, 0, 'R', 0, 2, '', '', true, 0);
            $pdf::SetFont('times', '', 8);

            $pdf::SetXY(25,12);
            $pdf::MultiCell(134, 5, $company_address, 0, 'C', 0, 2, '', '', true, 0);
            $pdf::MultiCell(44, 5, $data['PurchaseNumber'], 0, 'R', 0, 2, '', '', true, 0);

            $pdf::SetXY(25,16);
            $pdf::MultiCell(147, 5, $company_mobile, 0, 'C', 0, 2, '', '', true, 0);

            $pdf::SetXY(25,20);
            $pdf::MultiCell(107, 5, $company_email, 0, 'C', 0, 2, '', '', true, 0);
            $pdf::MultiCell(71, 5, 'Date : '.date('d-m-Y', strtotime($data['PurchaseDate'])), 0, 'R', 0, 2, '', '', true, 0);

            $pdf::SetXY(25,24);
            $pdf::MultiCell(106, 5, 'TRN : 100330389600003', 0, 'C', 0, 2, '', '', true, 0);
            $pdf::MultiCell(72, 5, 'Due Date : '.date('d-m-Y', strtotime($data['DueDate'])), 0, 'R', 0, 2, '', '', true, 0);

            $pdf::SetXY(28,28);
            $pdf::Image('https://watanpharma.com/images/logo-1.png', 15, 5, 40, 18, 'PNG', '', '', true, 300, '', false, false, 0, false, false, false);
            $pdf::SetXY(15,37);
            $pdf::Ln(6);

            $pdf::SetXY(25,35);
            $pdf::writeHTML("<hr>", true, false, false, false, '');

            $row=$data['purchase_details'];
            $pdf::SetFont('times', '', 15);
            $html='<u><b>PURCHASE INVOICE</b></u>';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $created_by=isset($data['user']['name'])?$data['user']['name']:'N.A.';
            $vendor=isset($data['supplier']['Name'])?$data['supplier']['Name']:'N.A.';
            $email=isset($data['vendor']['Name'])?$data['vendor']['Name']:'N.A.';
            $phone=isset($data['vendor']['Mobile'])?$data['vendor']['Mobile']:'N.A.';
            $address=isset($data['vendor']['Address'])?$data['vendor']['Address']:'N.A.';
            $pdf::SetFont('times', '', 10);
            $pdf::Cell(95, 5, 'SUPPLIER :','B',0,'L');
            $pdf::Cell(95, 5, 'Created By : '.$created_by,'',0,'R');
            $pdf::Ln(6);

            $pdf::Cell(95, 5, 'Name : '.$vendor,'',0,'L');
            $pdf::Ln(6);

            $pdf::Cell(95, 5, 'Email : '.$email,'',0,'L');
            $pdf::Ln(6);

            $pdf::Cell(95, 5, 'Phone : '.$phone,'',0,'L');
            $pdf::Ln(6);

            $pdf::Cell(95, 5, 'Address : '.$address,'',0,'L');
            $pdf::Ln(6);

            $pdf::Cell(95, 5, '','',0,'');
            $pdf::Ln(6);

            $pdf::SetFont('times', 'B', 14);
            $html = '<table border="0.5" cellpadding="5">
                <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                    <th align="center" width="30">S/N</th>
                    <th align="center" width="190">Product</th>
                    <th align="center" width="70">PadNO</th>
                    <th align="center" width="50">Unit</th>
                    <th align="center" width="55">Price</th>
                    <th align="center" width="50">Quantity</th>
                    <th align="center" width="35">VAT</th>
                    <th align="center" width="80">Subtotal</th>
                </tr>';
            $pdf::SetFont('times', '', 10);
            $subtotal=0.0;
            $vat_total=0.0;
            $grand_total=0.0;
            $sn=0;
            for($i=0;$i<count($row);$i++)
            {
//                if($row[$i]['deleted_at']=='1970-01-01T08:00:00.000000Z')
//                {

                $html .='<tr>
                    <td align="center" width="30">'.($sn+1).'</td>
                    <td align="left" width="190">'.$row[$i]['api_product']['Name'].'</td>
                    <td align="left" width="70">'.$row[$i]['PadNumber'].'</td>
                    <td align="center" width="50">'.'N.A.'.'</td>
                    <td align="center" width="55">'.number_format($row[$i]['Price'],2,'.',',').'</td>
                    <td align="center" width="50">'.number_format($row[$i]['Quantity'],2,'.',',').'</td>
                    <td align="center" width="35">'.number_format($row[$i]['VAT'],2,'.',',').'</td>
                    <td align="right" width="80">'.number_format($row[$i]['rowSubTotal'],2,'.',',').'</td>
                    </tr>';
                $sn++;
                //}
            }
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table><table border="0" cellpadding="5">';
            $html.= '
                <tr color="black">
                    <td width="220" colspan="2" style="border: 1px solid black;">Terms & Conditions :</td>
                    <td width="175" colspan="4" style="border: 1px solid black;">Vendor Note :</td>
                    <td width="85" colspan="2" align="right" style="border: 1px solid black;">Total(AED)</td>
                    <td width="80" align="right" style="border: 1px solid black;">'.number_format($subtotal,2,'.',',').'</td>
                </tr>';
            $terms_condition=isset($data['TermsAndCondition'])?$data['TermsAndCondition']:'N.A.';
            $vendor_note=isset($data['supplierNote'])?$data['supplierNote']:'N.A.';
            $html.= '
                <tr color="black">
                    <td width="220" colspan="2" rowspan="2" style="border: 1px solid black;">'.$data['TermsAndCondition'].'</td>
                    <td width="175" colspan="4" rowspan="2" style="border: 1px solid black;">'.$data['supplierNote'].'</td>
                    <td width="85" colspan="2" align="right" style="border: 1px solid black;">VAT (5%)</td>
                    <td width="80" align="right" style="border: 1px solid black;">'.number_format($data['totalVat'],2,'.',',').'</td>
                </tr>';
            $html.= '
                <tr color="black">
                    <td width="85" colspan="2" align="right" style="border: 1px solid black;">Grand Total(AED)</td>
                    <td width="80" align="right" style="border: 1px solid black;">'.number_format($data['grandTotal'],2,'.',',').'</td>
                </tr>';
            $html.='</table>';
            $pdf::writeHTML($html, true, false, true, false, '');

            $amount_in_words=Str::getUAECurrency($data['grandTotal']);
            $pdf::Cell(95, 5, 'Amount in Words : '.$amount_in_words,'',0,'L');
            $pdf::Ln(6);
            $pdf::Ln(6);
            $pdf::Ln(6);
            $pdf::Ln(6);

            $pdf::writeHTML("<hr>", true, false, false, false, '');

            $pdf::Cell(95, 5, 'Accepted By (Name & Signature) :','',0,'C');
            $pdf::Cell(95, 5, 'Issued By (Name & Signature): ','',0,'C');

            $pdf::lastPage();
            $time=time();
            if (!file_exists('/app/public/purchase_order_files/')) {
                mkdir('/app/public/purchase_order_files/', 0777, true);
            }
            $fileLocation = storage_path().'/app/public/purchase_order_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/purchase_order_files/'.$time.'.pdf';
            //$url=storage_path().'/purchase_order_files/'.$time.'.pdf';
            //$url=array('url'=>$url);
            return $url;
        }
        else
        {
            return FALSE;
        }
    }

    public function supplierSaleDetails($Id)
    {
        // TODO: Implement supplierSaleDetails() method.
        $sales = Purchase::with('supplier','purchase_details')->where([
                'supplier_id'=>$Id,
                'IsPaid'=> false,
            ])->get();
        return response()->json($sales);
    }
}
