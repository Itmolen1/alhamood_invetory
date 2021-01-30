<?php

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

class   SaleRepository implements ISaleRepositoryInterface
{

    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(Sale::with('sale_details.product','sale_details.vehicle','customer')->where('company_id',session('company_id'))->latest()->get())

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
        $saleNo = $this->invoiceNumber();
        $PadNumber = $this->PadNumber();
        $customers = Customer::with('customer_prices')->get();
        $products = Product::all();
        $salesRecords = Sale::with('sale_details.vehicle','customer')->where('company_id',session('company_id'))->orderBy('id', 'desc')->skip(0)->take(3)->get();
        //dd($saleNo);
        return view('admin.sale.create',compact('customers','saleNo','products','salesRecords','PadNumber'));
    }

    public function store(Request $request)
    {
        $AllRequestCount = collect($request->Data)->count();
        if($AllRequestCount > 0)
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
                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                $difference = $cashTransaction->last()->Differentiate;
                $cash_transaction = new CashTransaction();
                $cash_transaction->Reference=$sale;
                $cash_transaction->createdDate=$request->Data['SaleDate'];
                $cash_transaction->Type='sales';
                $cash_transaction->Details='CashSales|'.$sale;
                $cash_transaction->Credit=0.00;
                $cash_transaction->Debit=$request->Data['paidBalance'];
                $cash_transaction->Differentiate=$difference+$request->Data['paidBalance'];
                $cash_transaction->user_id = $user_id;
                $cash_transaction->company_id = $company_id;
                $cash_transaction->save();
            }

            ////////////////// start account section gautam ////////////////
            if($sale)
            {
                $accountTransaction = AccountTransaction::where(['customer_id'=> $request->Data['customer_id'],])->get();
                // totally credit
                if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00) {
                    $totalCredit = $request->Data['grandTotal'];
                    $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                    $AccData =
                        [
                            'customer_id' => $request->Data['customer_id'],
                            'Credit' => 0.00,
                            'Debit' => $totalCredit,
                            'Differentiate' => $difference,
                            'createdDate' => date('Y-m-d'),
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'Sales|'.$sale,
                            'referenceNumber'=>'P#'.$detail['PadNumber'],
                        ];
                    $AccountTransactions = AccountTransaction::Create($AccData);
                }
                // partial payment some cash some credit
                elseif($request->Data['paidBalance'] > 0 AND $request->Data['paidBalance'] < $request->Data['grandTotal'] )
                {
                    $differenceValue = $accountTransaction->last()->Differentiate - $request->Data['paidBalance'];
                    $totalCredit = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                    $difference = $differenceValue + $request->Data['grandTotal'];

                    //make debit entry for the sales
                    $AccData =
                        [
                            'customer_id' => $request->Data['customer_id'],
                            'Credit' => 0.00,
                            'Debit' => $request->Data['grandTotal'],
                            'Differentiate' => $totalCredit,
                            'createdDate' => $request->Data['SaleDate'],
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'Sales|'.$sale,
                            'referenceNumber'=>'P#'.$detail['PadNumber'],
                        ];
                    $AccountTransactions = AccountTransaction::Create($AccData);

                    //make credit entry for the whatever cash is paid
                    $difference=$totalCredit-$request->Data['paidBalance'];
                    $AccData =
                        [
                            'customer_id' => $request->Data['customer_id'],
                            'Credit' => $request->Data['paidBalance'],
                            'Debit' => 0.00,
                            'Differentiate' => $difference,
                            'createdDate' => date('Y-m-d'),
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'PartialCashSales|'.$sale,
                            'referenceNumber'=>'P#'.$detail['PadNumber'],
                        ];
                    $AccountTransactions = AccountTransaction::Create($AccData);
                }
                // fully paid with cash
                else
                {
                    $totalCredit = $request->Data['paidBalance'];
                    $difference = $accountTransaction->last()->Differentiate + $request->Data['paidBalance'];

                    //make credit entry for the sales
                    $AccountTransactions=AccountTransaction::Create([
                        'customer_id' => $request->Data['customer_id'],
                        'Credit' => 0.00,
                        'Debit' => $totalCredit,
                        'Differentiate' => $difference,
                        'createdDate' => $request->Data['SaleDate'],
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description'=>'Sales|'.$sale,
                        'referenceNumber'=>'P#'.$detail['PadNumber'],
                    ]);

                    //make credit entry for the whatever cash is paid
                    $difference=$difference-$request->Data['paidBalance'];
                    $AccountTransactions=AccountTransaction::Create([
                        'customer_id' => $request->Data['customer_id'],
                        'Credit' => $request->Data['paidBalance'],
                        'Debit' => 0.00,
                        'Differentiate' => $difference,
                        'createdDate' => date('Y-m-d'),
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description'=>'FullCashSales|'.$sale,
                        'referenceNumber'=>'P#'.$detail['PadNumber'],
                    ]);
                }
                return Response()->json($AccountTransactions);
                // return Response()->json("");
            }
            ////////////////// end account section gautam ////////////////
        }
    }

    public function update(Request $request, $Id)
    {
        $AllRequestCount = collect($request->Data)->count();
        if($AllRequestCount > 0)
        {
            $sold = Sale::with('customer.account_transaction')->find($Id);
            $user_id = session('user_id');
            $company_id = session('company_id');

            ////////////////// account section gautam ////////////////
            $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
            if (!is_null($accountTransaction))
            {
                // if paid balance is not same as earlier need to update cash account as well
                if($sold->paidBalance!=$request->Data['paidBalance'])
                {
                    //check if previously cash transaction done with this sales id
                    $description_string='CashSales|'.$Id;
                    $previous_cash_entry = CashTransaction::get()->where('company_id','=',$company_id)->where('Details','like',$description_string)->last();
                    if($previous_cash_entry)
                    {
                        // start reverse entry
                        $previously_debited = $previous_cash_entry->Debit;
                        $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                        $difference = $cashTransaction->last()->Differentiate;
                        $cash_transaction = new CashTransaction();
                        $cash_transaction->Reference=$Id;
                        $cash_transaction->createdDate=$request->Data['SaleDate'];
                        $cash_transaction->Type='sales';
                        $cash_transaction->Details='CashSales|'.$Id.'hide';
                        $cash_transaction->Credit=$previously_debited;
                        $cash_transaction->Debit=0.00;
                        $cash_transaction->Differentiate=$difference-$previously_debited;
                        $cash_transaction->user_id = $user_id;
                        $cash_transaction->company_id = $company_id;
                        $cash_transaction->save();
                        // also hide previous entry start
                        CashTransaction::where('id', $previous_cash_entry->id)->update(array('Details' => 'CashSales|'.$Id.'hide'));
                        // also hide previous entry end
                        // reverse entry done

                        // start new entry
                        $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                        $difference = $cashTransaction->last()->Differentiate;
                        $cash_transaction = new CashTransaction();
                        $cash_transaction->Reference=$Id;
                        $cash_transaction->createdDate=$request->Data['SaleDate'];
                        $cash_transaction->Type='sales';
                        $cash_transaction->Details='CashSales|'.$Id;
                        $cash_transaction->Credit=0.00;
                        $cash_transaction->Debit=$request->Data['paidBalance'];
                        $cash_transaction->Differentiate=$difference+$request->Data['paidBalance'];
                        $cash_transaction->user_id = $user_id;
                        $cash_transaction->company_id = $company_id;
                        $cash_transaction->save();
                        // end new entry

                        // now here we check if only and only cash paid is updating and none of the below case will execute then we need..
                        // to check if there any existing entry with PartialCashSales|$id and not hidden we need to reverse that entry
                        if($request->Data['customer_id']==$sold->customer_id  AND $sold->grandTotal==$request->Data['grandTotal'])
                        {
                            $description_string='PartialCashSales|'.$Id;
                            $previous_entry = AccountTransaction::get()->where('company_id','=',$company_id)->where('Description','like',$description_string)->last();
                            if($previous_entry)
                            {
                                // start revers entry
                                $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                                $last_closing=$accountTransaction->last()->Differentiate;
                                $previously_credited = $previous_entry->Credit;
                                $AccData =
                                    [
                                        'customer_id' => $sold->customer_id,
                                        'Debit' => $previously_credited,
                                        'Credit' => 0.00,
                                        'Differentiate' => $last_closing+$previously_credited,
                                        'createdDate' => date('Y-m-d'),
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'PartialCashSales|'.$Id,
                                        'updateDescription'=>'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done

                                // start new entry
                                $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                                $last_closing=$accountTransaction->last()->Differentiate;
                                $AccData =
                                    [
                                        'customer_id' => $sold->customer_id,
                                        'Debit' => 0.00,
                                        'Credit' => $request->Data['paidBalance'],
                                        'Differentiate' => $last_closing-$request->Data['paidBalance'],
                                        'createdDate' => date('Y-m-d'),
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'PartialCashSales|'.$Id,
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
                        $cash_transaction->createdDate=$request->Data['SaleDate'];
                        $cash_transaction->Type='sales';
                        $cash_transaction->Details='CashSales|'.$Id;
                        $cash_transaction->Credit=0.00;
                        $cash_transaction->Debit=$request->Data['paidBalance'];
                        $cash_transaction->Differentiate=$difference+$request->Data['paidBalance'];
                        $cash_transaction->user_id = $user_id;
                        $cash_transaction->company_id = $company_id;
                        $cash_transaction->save();
                        // end new entry

                        // now here we check if only and only cash paid is updating and none of the below case will execute then we need..
                        // to create one more cash entry for this sales as partial cash sales entry in account transaction
                        if($request->Data['customer_id']==$sold->customer_id  AND $sold->grandTotal==$request->Data['grandTotal'])
                        {
                            $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                            $last_closing=$accountTransaction->last()->Differentiate;
                            $description_string='Sales|'.$Id;
                            $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_debited = $previous_entry->Debit;
                            $AccData =
                                [
                                    'customer_id' => $sold->customer_id,
                                    'Debit' => 0.00,
                                    'Credit' => $request->Data['paidBalance'],
                                    'Differentiate' => $last_closing-$request->Data['paidBalance'],
                                    'createdDate' => date('Y-m-d'),
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'PartialCashSales|'.$Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                        }
                    }
                }

                // here will come 3 cases
                // 1. only customer is updating - quantity and price remains same
                // 2. only quantity or price updating - customer is the same
                // 3. both customer and quantity or price updating

                // 1 check if only customer is changed and not quantity or price = grand total is same as previous
                if($request->Data['customer_id']!=$sold->customer_id  AND $sold->grandTotal==$request->Data['grandTotal'])
                {
                    //customer is changed need to reverse all previously made account entries for the previous customer

                    //case : 1 full credit entry + payment is not done yet like isPaid=0 and IsPartialPaid=0
                    if($sold->IsPaid==0 && $sold->IsPartialPaid==0)
                    {
                        // start reverse entry
                        $last_closing=$accountTransaction->last()->Differentiate;
                        $description_string='Sales|'.$Id;
                        $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                        $previously_debited = $previous_entry->Debit;
                        $AccData =
                            [
                                'customer_id' => $sold->customer_id,
                                'Debit' => 0.00,
                                'Credit' => $previously_debited,
                                'Differentiate' => $last_closing-$previously_debited,
                                'createdDate' => date('Y-m-d'),
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$Id,
                                'updateDescription'=>'hide',
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // also hide previous entry start
                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                        // also hide previous entry end
                        // reverse entry done
                    }
                    //case : 2 partial cash is paid and some amount is credit + payment is not fully done yet like isPaid=0 and IsPartialPaid=1
                    elseif($sold->paidBalance > 0 AND $sold->paidBalance < $sold->grandTotal AND $sold->IsPartialPaid==1)
                    {
                        // entry 1 : debit entry for sales
                        // start reverse entry
                        $last_closing=$accountTransaction->last()->Differentiate;
                        $description_string='Sales|'.$Id;
                        $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                        //echo "<pre>";print_r($previous_entry->Credit);die;
                        $previously_debited = $previous_entry->Debit;
                        $AccData =
                            [
                                'customer_id' => $sold->customer_id,
                                'Debit' => 0.00,
                                'Credit' => $previously_debited,
                                'Differentiate' => $last_closing-$previously_debited,
                                'createdDate' => date('Y-m-d'),
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$Id,
                                'updateDescription'=>'hide',
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // also hide previous entry start
                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                        // also hide previous entry end
                        // reverse entry done
                        // entry 2 : credit whatever cash is debited
                        // start reverse entry
                        $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                        $last_closing=$accountTransaction->last()->Differentiate;
                        $description_string='PartialCashSales|'.$Id;
                        $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                        //echo "<pre>";print_r($previous_entry->Credit);die;
                        $previously_credited = $previous_entry->Credit;
                        $AccData =
                            [
                                'customer_id' => $sold->customer_id,
                                'Debit' => $previously_credited,
                                'Credit' => 0.00,
                                'Differentiate' => $last_closing+$previously_credited,
                                'createdDate' => date('Y-m-d'),
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'PartialCashSales|'.$Id,
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
                        // entry 1 : debit entry for sales
                        // start reverse entry
                        $last_closing=$accountTransaction->last()->Differentiate;
                        $description_string='Sales|'.$Id;
                        $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                        //echo "<pre>";print_r($previous_entry->Credit);die;
                        $previously_debited = $previous_entry->Debit;
                        $AccData =
                            [
                                'customer_id' => $sold->customer_id,
                                'Debit' => 0.00,
                                'Credit' => $previously_debited,
                                'Differentiate' => $last_closing-$previously_debited,
                                'createdDate' => date('Y-m-d'),
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$Id,
                                'updateDescription'=>'hide',
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // also hide previous entry start
                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                        // also hide previous entry end
                        // reverse entry done
                        // entry 2 : credit whatever cash is debited
                        // start reverse entry
                        $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                        $last_closing=$accountTransaction->last()->Differentiate;
                        $description_string='FullCashSales|'.$Id;
                        $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                        $previously_credited = $previous_entry->Credit;
                        $AccData =
                            [
                                'customer_id' => $sold->customer_id,
                                'Debit' => $previously_credited,
                                'Credit' => 0.00,
                                'Differentiate' => $last_closing+$previously_credited,
                                'createdDate' => date('Y-m-d'),
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'FullCashSales|'.$Id,
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
                    // start new entry for updated customer with checking all three cases
                    $accountTransaction = AccountTransaction::where(['customer_id'=> $request->Data['customer_id'],])->get();
                    // totally credit
                    if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00) {
                        $totalCredit = $request->Data['grandTotal'];
                        $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                        $AccData =
                            [
                                'customer_id' => $request->Data['customer_id'],
                                'Credit' => 0.00,
                                'Debit' => $totalCredit,
                                'Differentiate' => $difference,
                                'createdDate' => date('Y-m-d'),
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$request->Data['orders'][0]['PadNumber'],
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                    }
                    // partial payment some cash some credit
                    elseif($request->Data['paidBalance'] > 0 AND $request->Data['paidBalance'] < $request->Data['grandTotal'] )
                    {
                        $differenceValue = $accountTransaction->last()->Differentiate - $request->Data['paidBalance'];
                        $totalCredit = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                        $difference = $differenceValue + $request->Data['grandTotal'];

                        //make credit entry for the sales
                        $AccData =
                            [
                                'customer_id' => $request->Data['customer_id'],
                                'Credit' => 0.00,
                                'Debit' => $request->Data['grandTotal'],
                                'Differentiate' => $totalCredit,
                                'createdDate' => date('Y-m-d'),
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$request->Data['orders'][0]['PadNumber'],
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);

                        //make debit entry for the whatever cash is paid
                        $difference=$totalCredit-$request->Data['paidBalance'];
                        $AccData =
                            [
                                'customer_id' => $request->Data['customer_id'],
                                'Credit' => $request->Data['paidBalance'],
                                'Debit' => 0.00,
                                'Differentiate' => $difference,
                                'createdDate' => date('Y-m-d'),
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'PartialCashSales|'.$request->Data['orders'][0]['PadNumber'],
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                    }
                    // fully paid with cash
                    else
                    {
                        $totalCredit = $request->Data['grandTotal'];
                        $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                        //make credit entry for the sales
                        $AccountTransactions=AccountTransaction::Create([
                            'customer_id' => $request->Data['customer_id'],
                            'Credit' => 0.00,
                            'Debit' => $totalCredit,
                            'Differentiate' => $difference,
                            'createdDate' => date('Y-m-d'),
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'Sales|'.$request->Data['orders'][0]['PadNumber'],
                        ]);

                        //make debit entry for the whatever cash is paid
                        $difference=$difference-$request->Data['paidBalance'];
                        $AccountTransactions=AccountTransaction::Create([
                            'customer_id' => $request->Data['customer_id'],
                            'Credit' => $request->Data['paidBalance'],
                            'Debit' => 0.00,
                            'Differentiate' => $difference,
                            'createdDate' => date('Y-m-d'),
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'FullCashSales|'.$request->Data['orders'][0]['PadNumber'],
                        ]);
                    }
                    /*new entry*/
                }
                // check if only grand total is changed and not the customer
                elseif($request->Data['customer_id']==$sold->customer_id  AND $sold->grandTotal!=$request->Data['grandTotal'])
                {
                    //customer is not changed then need to find what is the differance in total and for payment changes
                    // here in two way we can proceed
                    // option 1 : reverse previous account entries and make new entry
                    // option 2 : find out plus minus differance and make one another entry with differences
                    // option 2 is not preferable because of while displaying we need to add or subtract similar sales id entry so that is little tricky in query
                    // also need to manage isPaid and isPartialPaid flag according

                    // implementation of option 2
                    //case : 1 full credit entry + payment is not done yet like isPaid=0 and IsPartialPaid=0
                    if($sold->IsPaid==0 && $sold->IsPartialPaid==0)
                    {
                        // start reverse entry
                        $last_closing=$accountTransaction->last()->Differentiate;
                        $description_string='Sales|'.$Id;
                        $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                        $previously_debited = $previous_entry->Debit;
                        $AccData =
                            [
                                'customer_id' => $sold->customer_id,
                                'Debit' => 0.00,
                                'Credit' => $previously_debited,
                                'Differentiate' => $last_closing-$previously_debited,
                                'createdDate' => date('Y-m-d'),
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$Id,
                                'updateDescription'=>'hide',
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // also hide previous entry start
                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                        // also hide previous entry end
                        // reverse entry done
                    }
                    //case : 2 partial cash is paid and some amount is credit + payment is not fully done yet like isPaid=0 and IsPartialPaid=1
                    elseif($sold->paidBalance > 0 AND $sold->paidBalance < $sold->grandTotal AND $sold->IsPartialPaid==1)
                    {
                        // entry 1 : debit entry for sales
                        // start reverse entry
                        $last_closing=$accountTransaction->last()->Differentiate;
                        $description_string='Sales|'.$Id;
                        $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                        $previously_debited = $previous_entry->Debit;
                        $AccData =
                            [
                                'customer_id' => $sold->customer_id,
                                'Debit' => 0.00,
                                'Credit' => $previously_debited,
                                'Differentiate' => $last_closing-$previously_debited,
                                'createdDate' => date('Y-m-d'),
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$Id,
                                'updateDescription'=>'hide',
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // also hide previous entry start
                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                        // also hide previous entry end
                        // reverse entry done
                        // entry 2 : credit whatever cash is debited
                        // start reverse entry
                        $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                        $last_closing=$accountTransaction->last()->Differentiate;
                        $description_string='PartialCashSales|'.$Id;
                        $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                        //echo "<pre>";print_r($previous_entry->Credit);die;
                        $previously_credited = $previous_entry->Credit;
                        $AccData =
                            [
                                'customer_id' => $sold->customer_id,
                                'Debit' => $previously_credited,
                                'Credit' => 0.00,
                                'Differentiate' => $last_closing+$previously_credited,
                                'createdDate' => date('Y-m-d'),
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'PartialCashSales|'.$Id,
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
                        // entry 1 : debit entry for sales
                        // start reverse entry
                        $last_closing=$accountTransaction->last()->Differentiate;
                        $description_string='Sales|'.$Id;
                        $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                        $previously_debited = $previous_entry->Debit;
                        $AccData =
                            [
                                'customer_id' => $sold->customer_id,
                                'Debit' => 0.00,
                                'Credit' => $previously_debited,
                                'Differentiate' => $last_closing-$previously_debited,
                                'createdDate' => date('Y-m-d'),
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$Id,
                                'updateDescription'=>'hide',
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // also hide previous entry start
                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                        // also hide previous entry end
                        // reverse entry done
                        // entry 2 : credit whatever cash is debited
                        // start reverse entry
                        $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                        $last_closing=$accountTransaction->last()->Differentiate;
                        $description_string='FullCashSales|'.$Id;
                        $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                        //echo "<pre>";print_r($previous_entry->Credit);die;
                        $previously_credited = $previous_entry->Credit;
                        $AccData =
                            [
                                'customer_id' => $sold->customer_id,
                                'Debit' => $previously_credited,
                                'Credit' => 0.00,
                                'Differentiate' => $last_closing+$previously_credited,
                                'createdDate' => date('Y-m-d'),
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'FullCashSales|'.$Id,
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
                    // start new entry for updated customer with checking all three cases
                    $accountTransaction = AccountTransaction::where(['customer_id'=> $request->Data['customer_id'],])->get();
                    // totally credit
                    if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00) {
                        $totalCredit = $request->Data['grandTotal'];
                        $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                        $AccData =
                            [
                                'customer_id' => $request->Data['customer_id'],
                                'Credit' => 0.00,
                                'Debit' => $totalCredit,
                                'Differentiate' => $difference,
                                'createdDate' => date('Y-m-d'),
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$request->Data['orders'][0]['PadNumber'],
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                    }
                    // partial payment some cash some credit
                    elseif($request->Data['paidBalance'] > 0 AND $request->Data['paidBalance'] < $request->Data['grandTotal'] )
                    {
                        $differenceValue = $accountTransaction->last()->Differentiate - $request->Data['paidBalance'];
                        $totalCredit = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                        $difference = $differenceValue + $request->Data['grandTotal'];

                        //make credit entry for the sales
                        $AccData =
                            [
                                'customer_id' => $request->Data['customer_id'],
                                'Credit' => 0.00,
                                'Debit' => $request->Data['grandTotal'],
                                'Differentiate' => $totalCredit,
                                'createdDate' => date('Y-m-d'),
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$request->Data['orders'][0]['PadNumber'],
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);

                        //make debit entry for the whatever cash is paid
                        $difference=$totalCredit-$request->Data['paidBalance'];
                        $AccData =
                            [
                                'customer_id' => $request->Data['customer_id'],
                                'Credit' => $request->Data['paidBalance'],
                                'Debit' => 0.00,
                                'Differentiate' => $difference,
                                'createdDate' => date('Y-m-d'),
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'PartialCashSales|'.$request->Data['orders'][0]['PadNumber'],
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                    }
                    // fully paid with cash or there may be some advance amount remains
                    else
                    {
                        $totalCredit = $request->Data['grandTotal'];
                        $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                        //make credit entry for the sales
                        $AccountTransactions=AccountTransaction::Create([
                            'customer_id' => $request->Data['customer_id'],
                            'Credit' => 0.00,
                            'Debit' => $totalCredit,
                            'Differentiate' => $difference,
                            'createdDate' => date('Y-m-d'),
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'Sales|'.$request->Data['orders'][0]['PadNumber'],
                        ]);

                        //make debit entry for the whatever cash is paid
                        $difference=$difference-$request->Data['paidBalance'];
                        $AccountTransactions=AccountTransaction::Create([
                            'customer_id' => $request->Data['customer_id'],
                            'Credit' => $request->Data['paidBalance'],
                            'Debit' => 0.00,
                            'Differentiate' => $difference,
                            'createdDate' => date('Y-m-d'),
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'FullCashSales|'.$request->Data['orders'][0]['PadNumber'],
                        ]);
                    }
                    /*new entry*/
                }
                // check both supplier and grandTotal is changed meaning case 3
                elseif($request->Data['customer_id']!=$sold->customer_id  AND $sold->grandTotal!=$request->Data['grandTotal'])
                {
                    // if paid balance is not same as earlier need to update cash account as well
                    //case : 1 full credit entry + payment is not done yet like isPaid=0 and IsPartialPaid=0
                    if($sold->IsPaid==0 && $sold->IsPartialPaid==0)
                    {
                        // start reverse entry
                        $last_closing=$accountTransaction->last()->Differentiate;
                        $description_string='Sales|'.$Id;
                        $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                        $previously_debited = $previous_entry->Debit;
                        $AccData =
                            [
                                'customer_id' => $sold->customer_id,
                                'Debit' => 0.00,
                                'Credit' => $previously_debited,
                                'Differentiate' => $last_closing-$previously_debited,
                                'createdDate' => date('Y-m-d'),
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$Id,
                                'updateDescription'=>'hide',
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // also hide previous entry start
                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                        // also hide previous entry end
                        // reverse entry done
                    }
                    //case : 2 partial cash is paid and some amount is credit + payment is not fully done yet like isPaid=0 and IsPartialPaid=1
                    elseif($sold->paidBalance > 0 AND $sold->paidBalance < $sold->grandTotal AND $sold->IsPartialPaid==1)
                    {
                        // entry 1 : debit entry for sales
                        // start reverse entry
                        $last_closing=$accountTransaction->last()->Differentiate;
                        $description_string='Sales|'.$Id;
                        $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                        $previously_debited = $previous_entry->Debit;
                        $AccData =
                            [
                                'customer_id' => $sold->customer_id,
                                'Debit' => 0.00,
                                'Credit' => $previously_debited,
                                'Differentiate' => $last_closing-$previously_debited,
                                'createdDate' => date('Y-m-d'),
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$Id,
                                'updateDescription'=>'hide',
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // also hide previous entry start
                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                        // also hide previous entry end
                        // reverse entry done
                        // entry 2 : credit whatever cash is debited
                        // start reverse entry
                        $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                        $last_closing=$accountTransaction->last()->Differentiate;
                        $description_string='PartialCashSales|'.$Id;
                        $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                        //echo "<pre>";print_r($previous_entry->Credit);die;
                        $previously_credited = $previous_entry->Credit;
                        $AccData =
                            [
                                'customer_id' => $sold->customer_id,
                                'Debit' => $previously_credited,
                                'Credit' => 0.00,
                                'Differentiate' => $last_closing+$previously_credited,
                                'createdDate' => date('Y-m-d'),
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'PartialCashSales|'.$Id,
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
                        // entry 1 : debit entry for sales
                        // start reverse entry
                        $last_closing=$accountTransaction->last()->Differentiate;
                        $description_string='Sales|'.$Id;
                        $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                        $previously_debited = $previous_entry->Debit;
                        $AccData =
                            [
                                'customer_id' => $sold->customer_id,
                                'Debit' => 0.00,
                                'Credit' => $previously_debited,
                                'Differentiate' => $last_closing-$previously_debited,
                                'createdDate' => date('Y-m-d'),
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$Id,
                                'updateDescription'=>'hide',
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // also hide previous entry start
                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                        // also hide previous entry end
                        // reverse entry done

                        // entry 2 : credit whatever cash is debited
                        // start reverse entry
                        $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                        $last_closing=$accountTransaction->last()->Differentiate;
                        $description_string='FullCashSales|'.$Id;
                        $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                        $previously_credited = $previous_entry->Credit;
                        $AccData =
                            [
                                'customer_id' => $sold->customer_id,
                                'Debit' => $previously_credited,
                                'Credit' => 0.00,
                                'Differentiate' => $last_closing+$previously_credited,
                                'createdDate' => date('Y-m-d'),
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'FullCashSales|'.$Id,
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
                    // start new entry for updated customer with checking all three cases
                    $accountTransaction = AccountTransaction::where(['customer_id'=> $request->Data['customer_id'],])->get();
                    // totally credit
                    if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00) {
                        $totalCredit = $request->Data['grandTotal'];
                        $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                        $AccData =
                            [
                                'customer_id' => $request->Data['customer_id'],
                                'Credit' => 0.00,
                                'Debit' => $totalCredit,
                                'Differentiate' => $difference,
                                'createdDate' => date('Y-m-d'),
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$request->Data['orders'][0]['PadNumber'],
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                    }
                    // partial payment some cash some credit
                    elseif($request->Data['paidBalance'] > 0 AND $request->Data['paidBalance'] < $request->Data['grandTotal'] )
                    {
                        $differenceValue = $accountTransaction->last()->Differentiate - $request->Data['paidBalance'];
                        $totalCredit = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                        $difference = $differenceValue + $request->Data['grandTotal'];

                        //make credit entry for the sales
                        $AccData =
                            [
                                'customer_id' => $request->Data['customer_id'],
                                'Credit' => 0.00,
                                'Debit' => $request->Data['grandTotal'],
                                'Differentiate' => $totalCredit,
                                'createdDate' => date('Y-m-d'),
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$request->Data['orders'][0]['PadNumber'],
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);

                        //make debit entry for the whatever cash is paid
                        $difference=$totalCredit-$request->Data['paidBalance'];
                        $AccData =
                            [
                                'customer_id' => $request->Data['customer_id'],
                                'Credit' => $request->Data['paidBalance'],
                                'Debit' => 0.00,
                                'Differentiate' => $difference,
                                'createdDate' => date('Y-m-d'),
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'PartialCashSales|'.$request->Data['orders'][0]['PadNumber'],
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                    }
                    // fully paid with cash
                    else
                    {
                        $totalCredit = $request->Data['grandTotal'];
                        $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                        //make credit entry for the sales
                        $AccountTransactions=AccountTransaction::Create([
                            'customer_id' => $request->Data['customer_id'],
                            'Credit' => 0.00,
                            'Debit' => $totalCredit,
                            'Differentiate' => $difference,
                            'createdDate' => date('Y-m-d'),
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'Sales|'.$request->Data['orders'][0]['PadNumber'],
                        ]);

                        //make debit entry for the whatever cash is paid
                        $difference=$difference-$request->Data['paidBalance'];
                        $AccountTransactions=AccountTransaction::Create([
                            'customer_id' => $request->Data['customer_id'],
                            'Credit' => $request->Data['paidBalance'],
                            'Debit' => 0.00,
                            'Differentiate' => $difference,
                            'createdDate' => date('Y-m-d'),
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'FullCashSales|'.$request->Data['orders'][0]['PadNumber'],
                        ]);
                    }
                    /*new entry*/
                }
                //return Response()->json($accountTransaction);
            }
            ////////////////// end of account section gautam ////////////////

            //here will come cash transaction record update if scenario will come by
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

            $sold->update(
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
        $invoice = new Sale();
        $lastInvoiceID = $invoice->orderByDesc('id')->pluck('id')->first();
        $newInvoiceID = 'INV-00'.($lastInvoiceID + 1);
        return $newInvoiceID;
    }

    public function PadNumber()
    {
//        $PadNumber = new SaleDetail();
//        $lastPad = $PadNumber->orderByDesc('PadNumber')->pluck('PadNumber')->first();
//        $newPad = ($lastPad + 1);
//        return $newPad;

        //new pad number generation according to company last pad
        $PadNumber = new SaleDetail();
        $lastPad = $PadNumber->where('company_id',session('company_id'))->orderByDesc('PadNumber')->pluck('PadNumber')->first();
        $newPad = ($lastPad + 1);
        return $newPad;
    }
}
