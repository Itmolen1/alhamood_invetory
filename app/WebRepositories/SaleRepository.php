<?php
/**
 * Created by PhpStorm.
 * User: rizwanafridi
 * Date: 11/23/20
 * Time: 14:14
 */

namespace App\WebRepositories;


use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Unit;
use App\Models\AccountTransaction;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\UpdateNote;
use App\WebRepositories\Interfaces\ISaleRepositoryInterface;
use Illuminate\Http\Request;

class SaleRepository implements ISaleRepositoryInterface
{

    public function index()
    {
        // TODO: Implement index() method.
        if(request()->ajax())
        {
            return datatables()->of(Sale::with('sale_details.product','sale_details.vehicle','customer')->latest()->get())

                ->addColumn('action', function ($data) {

                    $button = '<a href="'.route('sales.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    return $button;
                })
                 ->addColumn('createdDate', function($data) {
                        return $data->sale_details[0]->createdDate ?? "No date";
                    })
                 ->addColumn('PadNumber', function($data) {
                        return $data->sale_details[0]->PadNumber ?? "No Pad";
                    })
                 ->addColumn('customer', function($data) {
                        return $data->customer->Name ?? "No Name";
                    })
                 ->addColumn('registrationNumber', function($data) {
                        return $data->sale_details[0]->vehicle->registrationNumber ?? "No Number";
                    })
                 ->addColumn('Product', function($data) {
                        return $data->sale_details[0]->product->Name ?? "No product";
                    })
                  ->addColumn('Quantity', function($data) {
                        return $data->sale_details[0]->Quantity ?? "No Quantity";
                    })
                   ->addColumn('Price', function($data) {
                        return $data->sale_details[0]->Price ?? "No Quantity";
                    })
                ->rawColumns(
                    [
                    'action',
                    // 'isActive',
                    'createdDate',
                    'PadNumber',
                    'customer',
                    'registrationNumber',
                    'Product',
                    'Quantity',
                    'Price'
                    ])
                ->make(true);
        }
        return view('admin.sale.index');
    }

    public function create()
    {
        // TODO: Implement create() method.
        $saleNo = $this->invoiceNumber();
        $PadNumber = $this->PadNumber();
        $customers = Customer::with('customer_prices')->get();
        $products = Product::all();
        $salesRecords = Sale::with('sale_details.vehicle','customer')->orderBy('id', 'desc')->skip(0)->take(3)->get();
        //dd($saleNo);
        return view('admin.sale.create',compact('customers','saleNo','products','salesRecords','PadNumber'));
    }

    public function store(Request $request)
    {
        // TODO: Implement store() method.
        $AllRequestCount = collect($request->Data)->count();
        if($AllRequestCount > 0) {
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

            $user_id = session('user_id');
            $company_id = session('company_id');
            $sale = new Sale();
            $sale->SaleNumber = $request->Data['SaleNumber'];
            $sale->SaleDate = $request->Data['SaleDate'];
            $sale->Total = $request->Data['Total'];
            $sale->subTotal = $request->Data['subTotal'];
            $sale->totalVat = $request->Data['totalVat'];
            $sale->grandTotal = $request->Data['grandTotal'];
            $sale->paidBalance = $request->Data['paidBalance'];
            $sale->remainingBalance = $request->Data['remainingBalance'];
            $sale->customer_id = $request->Data['customer_id'];
            $sale->Description = $request->Data['customerNote'];
            $sale->IsPaid = $isPaid;
            $sale->IsPartialPaid = $partialPaid;
            $sale->IsReturn = false;
            $sale->IsPartialReturn = false;
            $sale->IsNeedStampOrSignature = false;
            $sale->user_id = $user_id;
            $sale->company_id = $company_id;
            $sale->save();
            $sale = $sale->id;
            foreach($request->Data['orders'] as $detail)
            {
                $data =  SaleDetail::create([
                    "product_id"        => $detail['product_id'],
                    "vehicle_id"        => $detail['vehicle_id'],
                    "unit_id"        => $detail['unit_id'],
                    "Quantity"        => $detail['Quantity'],
                    "Price"        => $detail['Price'],
                    "rowTotal"        => $detail['rowTotal'],
                    "VAT"        => $detail['Vat'],
                    "rowVatAmount"        => $detail['rowVatAmount'],
                    "rowSubTotal"        => $detail['rowSubTotal'],
                    "PadNumber"        => $detail['PadNumber'],
                    "company_id" => $company_id,
                    "user_id"      => $user_id,
                    "sale_id"      => $sale,
                    "createdDate" => $detail['createdDate'],
                ]);

            }

            if($request->Data['paidBalance'] != 0.00 || $request->Data['paidBalance'] != 0)
            {
                $cash_transaction = new CashTransaction();
                $cash_transaction->Reference=$request->Data['SaleNumber'];
                $cash_transaction->createdDate=date('Y-m-d h:i:s');
                $cash_transaction->Type='Sales';
                $cash_transaction->Credit=$request->Data['paidBalance'];
                $cash_transaction->Debit=0.0;
                $cash_transaction->save();
            }

            ////////////////// account section ////////////////
            if ($sale)
            {
               $accountTransaction = AccountTransaction::where(
                [
                    'customer_id'=> $request->Data['customer_id'],
                    'createdDate' => date('Y-m-d'),
                ])->first();
                if (!is_null($accountTransaction))
                {
                    if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00) {
                        if ($accountTransaction->createdDate != date('Y-m-d')) {
                            $totalCredit = $request->Data['grandTotal'];
                        } else {
                            $totalCredit = $accountTransaction->Credit + $request->Data['grandTotal'];
                        }
                        $totalDebit = $accountTransaction->Debit;
                        $difference = $accountTransaction->Differentiate + $request->Data['grandTotal'];
                    }
                    elseif($request->Data['paidBalance'] > 0 AND $request->Data['paidBalance'] < $request->Data['grandTotal'] )
                    {
                        if ($accountTransaction->createdDate != date('Y-m-d')) {
                            $totalDebit = $request->Data['paidBalance'];
                            $totalCredit = $request->Data['grandTotal'];
                        } else {
                            $totalDebit = $accountTransaction->Debit + $request->Data['paidBalance'];
                            $totalCredit = $accountTransaction->Credit + $request->Data['grandTotal'];
                        }
                        $differenceValue = $accountTransaction->Differentiate - $request->Data['paidBalance'];
                        $difference = $differenceValue + $request->Data['grandTotal'];
                    }
                    else{

                        if ($accountTransaction->createdDate != date('Y-m-d')) {
                            $totalDebit = $request->Data['paidBalance'];
                        } else {
                            $totalDebit = $accountTransaction->Debit + $request->Data['paidBalance'];
                        }
                        $totalCredit = $accountTransaction->Credit;
                        $difference = $accountTransaction->Differentiate - $request->Data['paidBalance'];
                    }
                }
                else
                {
                    $accountTransaction = AccountTransaction::where(
                    [
                        'customer_id'=> $request->Data['customer_id'],
                    ])->get();
                    if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00) {
                        $totalCredit = $request->Data['grandTotal'];
                        $totalDebit = $accountTransaction->last()->Debit;
                        $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                    }
                    elseif($request->Data['paidBalance'] > 0 AND $request->Data['paidBalance'] < $request->Data['grandTotal'] )
                    {

                        $totalDebit = $request->Data['paidBalance'];
                        $totalCredit = $request->Data['grandTotal'];
                        $differenceValue = $accountTransaction->last()->Differentiate - $request->Data['paidBalance'];
                        $difference = $differenceValue + $request->Data['grandTotal'];
                    }
                    else{
                        $totalDebit = $request->Data['paidBalance'];
                        $totalCredit = $accountTransaction->last()->Credit;
                        $difference = $accountTransaction->last()->Differentiate - $request->Data['paidBalance'];
                    }
                }
                $AccData =
                                   [
                                        'customer_id' => $request->Data['customer_id'],
                                        'Credit' => $totalCredit,
                                        'Debit' => $totalDebit,
                                        'Differentiate' => $difference,
                                        'createdDate' => date('Y-m-d'),
                                        'user_id' => $user_id,
                                   ];
                                   $AccountTransactions = AccountTransaction::updateOrCreate(
                                    [
                                        'createdDate'   => date('Y-m-d'),
                                        'customer_id'   => $request->Data['customer_id'],
                                    ],
                                     $AccData);
                                   return Response()->json($AccountTransactions);
                  // return Response()->json("");
            }
            ////////////////// end of account section ////////////////
        }
    }

    public function update(Request $request, $Id)
    {
        // TODO: Implement update() method.
        $AllRequestCount = collect($request->Data)->count();
        if($AllRequestCount > 0)
        {
            $saled = Sale::with('customer.account_transaction')->find($Id);
            $user_id = session('user_id');
            $company_id = session('company_id');


             ////////////////// account section ////////////////
               $accountTransaction = AccountTransaction::where(
                [
                    'customer_id'=> $request->Data['customer_id'],
                ])->get();
               if (!is_null($accountTransaction)) {
                            $lastAccountTransection = $accountTransaction->Last();
                        if ($lastAccountTransection->customer_id != $saled->customer_id)
                           {
                               if ($saled->paidBalance == 0 || $saled->paidBalance == 0.00) {
                                   $OldValue1 = $saled->customer->account_transaction->Last()->Credit - $saled->grandTotal;
                                   $OldTotalCredit = $OldValue1;
                                   $OldTotalDebit = $saled->customer->account_transaction->Last()->Debit;
                                   $OldValue = $saled->customer->account_transaction->Last()->Differentiate - $saled->grandTotal;
                                   $OldDifference = $OldValue;
                               }
                               elseif ($saled->paidBalance > 0 AND $saled->paidBalance < $saled->grandTotal)
                               {
                                   $OldTotalDebit = $saled->customer->account_transaction->Last()->Debit - $saled->paidBalance;
                                   $OldTotalCredit = $saled->customer->account_transaction->Last()->Credit - $saled->grandTotal;
                                   $differenceValue = $saled->customer->account_transaction->Last()->Differentiate + $saled->paidBalance;
                                   $OldDifference = $differenceValue - $saled->grandTotal;
                               }
                               else{
                                   $OldValue1 = $saled->customer->account_transaction->Last()->Debit - $saled->paidBalance;
                                   $OldTotalDebit = $OldValue1;
                                   $OldTotalCredit = $saled->customer->account_transaction->Last()->Credit;
                                   $OldValue = $saled->customer->account_transaction->Last()->Differentiate + $saled->paidBalance;
                                   $OldDifference = $OldValue;
                               }
                                    $OldAccData =
                                               [
                                                    'customer_id' => $saled->customer_id,
                                                    'Debit' => $OldTotalDebit,
                                                    'Credit' => $OldTotalCredit,
                                                    'Differentiate' => $OldDifference,
                                                    'createdDate' => $saled->customer->account_transaction->Last()->createdDate,
                                                    'user_id' =>$user_id,
                                               ];
                                               $AccountTransactions = AccountTransaction::updateOrCreate([
                                                'id'   => $saled->customer->account_transaction->Last()->id,
                                            ], $OldAccData);

                                    if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00) {
                                        $totalCredit = $lastAccountTransection->Credit + $request->Data['grandTotal'];
                                        $totalDebit = $lastAccountTransection->Debit;
                                        $difference = $lastAccountTransection->Differentiate + $request->Data['grandTotal'];
                                    }
                                    elseif ($request->Data['paidBalance'] > 0 AND $request->Data['paidBalance'] < $request->Data['grandTotal'])
                                    {
                                        $totalDebit = $lastAccountTransection->Debit - $request->Data['paidBalance'];
                                        $totalCredit = $lastAccountTransection->Credit - $request->Data['grandTotal'];
                                        $differenceValue = $accountTransaction->last()->Differentiate + $request->Data['paidBalance'];
                                        $difference = $differenceValue - $request->Data['grandTotal'];
                                    }
                                    else{
                                        $totalDebit = $lastAccountTransection->Debit + $request->Data['paidBalance'];
                                        $totalCredit = $lastAccountTransection->Credit;
                                        $difference = $lastAccountTransection->Differentiate - $request->Data['paidBalance'];
                                    }
                           }
                                else
                                {
                                    if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00 || $request->Data['paidBalance'] == "") {
                                        //return Response()->json("success");
                                        if ($lastAccountTransection->createdDate != $saled->customer->account_transaction->last()->createdDate) {
                                            $totalCredit = $request->Data['grandTotal'];
                                        } else {
                                            $value1 = $lastAccountTransection->Credit - $saled->grandTotal;
                                            $totalCredit = $value1 + $request->Data['grandTotal'];
                                        }
                                        $totalDebit = $lastAccountTransection->Debit;
                                        $value = $lastAccountTransection->Differentiate - $saled->grandTotal;
                                        $difference = $value + $request->Data['grandTotal'];
//                                        return Response()->json($difference);
                                    }
                                    elseif ($request->Data['paidBalance'] > 0 AND $request->Data['paidBalance'] < $request->Data['grandTotal'])
                                    {

                                        if ($lastAccountTransection->createdDate != $saled->customer->account_transaction->last()->createdDate) {
                                            $totalDebit = $request->Data['paidBalance'];
                                            $totalCredit = $request->Data['grandTotal'];
                                        } else {
                                            $value1 = $lastAccountTransection->Debit - $saled->paidBalance;
                                            $totalDebit = $value1 + $request->Data['paidBalance'];
                                            $valueC = $lastAccountTransection->Debit - $saled->grandTotal;
                                            $totalCredit = $valueC + $request->Data['grandTotal'];
                                        }
                                        $differenceValue = $lastAccountTransection->Differentiate - $request->Data['paidBalance'];
                                        $difference = $differenceValue + $request->Data['grandTotal'];
                                    }
                                    else{
                                        if ($lastAccountTransection->createdDate != $saled->customer->account_transaction->last()->createdDate) {
                                            $totalDebit = $request->Data['paidBalance'];
                                        } else {
                                            $value1 = $lastAccountTransection->Debit - $saled->paidBalance;
                                            $totalDebit = $value1 + $request->Data['paidBalance'];
                                        }
                                        $totalCredit = $lastAccountTransection->Credit;
                                        $value = $lastAccountTransection->Differentiate + $saled->paidBalance;
                                        $difference = $value - $request->Data['paidBalance'];
                                    }
                                }

                           $AccData =
                           [
                                'customer_id' => $request->Data['customer_id'],
                                'Credit' => $totalCredit,
                                'Debit' => $totalDebit,
                                'Differentiate' => $difference,
                                'createdDate' => $lastAccountTransection->createdDate,
                                'user_id' =>$user_id,
                           ];
                           $AccountTransactions = AccountTransaction::updateOrCreate([
                            'createdDate'   => $lastAccountTransection->createdDate,
                            'id'   => $lastAccountTransection->id,
                        ], $AccData);
                            //return Response()->json($accountTransaction);
               }
            ////////////////// end of account section ////////////////

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

            $saled->update(
                [
                    'SaleNumber' => $request->Data['SaleNumber'],
                    'SaleDate' => $request->Data['SaleDate'],
                    'Total' => $request->Data['Total'],
                    'subTotal' => $request->Data['subTotal'],
                    'totalVat' => $request->Data['totalVat'],
                    'grandTotal' => $request->Data['grandTotal'],
                    'paidBalance' => $request->Data['paidBalance'],
                    'remainingBalance' => $request->Data['remainingBalance'],
                    'customer_id' => $request->Data['customer_id'],
                    'Description' => $request->Data['Description'],
                    'IsPaid' => $isPaid,
                    'IsPartialPaid' => $partialPaid,
                    'IsReturn' => false,
                    'IsPartialReturn' => false,
                    'IsNeedStampOrSignature' => false,
                    'user_id' => $user_id,
                    'company_id' => $company_id,
                ]);

            $update_note = new UpdateNote();
            $update_note->RelationTable = 'sales';
            $update_note->RelationId = $Id;
            $update_note->Description = $request->Data['UpdateDescription'];
            $update_note->user_id = $user_id;
            $update_note->company_id = $company_id;
            $update_note->save();

            $d = SaleDetail::where('sale_id', array($Id))->delete();
            $slct = SaleDetail::where('sale_id', $Id)->get();
            foreach ($request->Data['orders'] as $detail)
            {
                $saleDetails = SaleDetail::create([
                    //"Id" => $detail['Id'],
                    "product_id"        => $detail['product_id'],
                    "unit_id"        => $detail['unit_id'],
                    "vehicle_id"        => $detail['vehicle_id'],
                    "Quantity"        => $detail['Quantity'],
                    "Price"        => $detail['Price'],
                    "rowTotal"        => $detail['rowTotal'],
                    "VAT"        => $detail['Vat'],
                    "rowVatAmount"        => $detail['rowVatAmount'],
                    "rowSubTotal"        => $detail['rowSubTotal'],
                    "PadNumber"        => $detail['PadNumber'],
                    "company_id" => $company_id,
                    "user_id"      => $user_id,
                    "sale_id"      => $Id,
                    "createdDate" => $detail['createdDate'],
                ]);
            }
            $ss = SaleDetail::where('sale_id', array($saleDetails['sale_id']))->get();
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
        $update_notes = UpdateNote::with('company','user')->where(['RelationId' => $Id, 'RelationTable' => 'sales'])->get();
        //dd($update_notes[0]->Description);
        $customers = Customer::all();
        $products = Product::all();
        $units = Unit::all();
        $sale_details = SaleDetail::withTrashed()->with('sale.customer.customer_prices','user','product','unit','vehicle')->where('sale_id', $Id)->get();
        return view('admin.sale.edit',compact('sale_details','customers','products','update_notes','units'));
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

        $invoice = new Sale();
        $lastInvoiceID = $invoice->orderByDesc('id')->pluck('id')->first();
        $newInvoiceID = 'INV-00'.($lastInvoiceID + 1);
        return $newInvoiceID;
    }

    public function PadNumber()
    {
        // TODO: Implement PadNumber() method.

        $PadNumber = new SaleDetail();
        $lastPad = $PadNumber->orderByDesc('PadNumber')->pluck('PadNumber')->first();
        $newPad = ($lastPad + 1);
        return $newPad;
    }
}
