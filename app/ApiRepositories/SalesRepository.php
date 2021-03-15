<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\ISalesRepositoryInterface;
use App\Http\Requests\SaleRequest;
use App\Http\Resources\Sales\SalesResource;
use App\Models\AccountTransaction;
use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\FileUpload;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\UpdateNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PDF;

class SalesRepository implements ISalesRepositoryInterface
{
    public function all()
    {
        return SalesResource::collection(Sale::with('sale_details')->get()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
//        $sql1="SELECT * FROM sale_details";
//        $row1 = DB::select( DB::raw($sql1));
//        $row1=json_decode(json_encode($row1), true);
//        //echo "<pre>";print_r($row1);die;
//
//        for($i=0;$i<count($row1);$i++)
//        {
//            $total=$row1[$i]['Quantity']*$row1[$i]['Price'];
//            $pad=$row1[$i]['PadNumber'];
//            $sql="UPDATE `sale_details` SET `rowTotal`= ".$total." WHERE `PadNumber`=".$pad;
//            DB::raw($sql);
//            unset($total);
//            unset($pad);
//            unset($sql);
//        }
//        echo "done";die;
//
//        $row=json_decode(json_encode($row), true);
//        $row=array_column($row,'id');
//
//        $sql1="SELECT sale_id FROM sale_details ";
//        $row1 = DB::select( DB::raw($sql1));
//        $row1=json_decode(json_encode($row1), true);
//        $row1=array_column($row1,'sale_id');
//
//        $result=array_diff($row,$row1);
//        echo "<pre>";print_r($result);die;

        $user_id = Auth::id();
        $company_id=Str::getCompany($user_id);
        return SalesResource::Collection(Sale::with('sale_details','update_notes','documents')->get()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(Request $request)
    {
        ////////////start new code////////////////////////////
        $sales_id=0;
        DB::transaction(function () use($request,&$sales_id){
            $user_id = Auth::id();
            $company_id=Str::getCompany($user_id);
            if ($request->paidBalance == 0.00 || $request->paidBalance == 0) {
                $isPaid_current = 0;
                $partialPaid_current = 0;
            }
            elseif($request->paidBalance==$request->grandTotal)
            {
                $isPaid_current = 1;
                $partialPaid_current = 0;
            }
            else
            {
                $isPaid_current = 0;
                $partialPaid_current = 1;
            }

            $invoice = new Sale();
            $lastInvoiceID = $invoice->orderByDesc('id')->pluck('id')->first();
            $newInvoiceID = 'INV-00'.($lastInvoiceID + 1);

            $sales = new Sale();
            $sales->SaleNumber=$newInvoiceID;
            $sales->customer_id=$request->customer_id;
            $sales->SaleDate=$request->SaleDate;
            $sales->DueDate=$request->SaleDate;
            $sales->referenceNumber=$request->referenceNumber;
            $sales->Total=$request->Total;
            $sales->subTotal=$request->subTotal;
            $sales->totalVat=$request->totalVat;
            $sales->grandTotal=$request->grandTotal;
            $sales->paidBalance=$request->paidBalance;
            $sales->remainingBalance=$request->remainingBalance;
            $sales->TermsAndCondition=$request->TermsAndCondition;
            $sales->supplierNote=$request->supplierNote;
            $sales->IsPaid=$isPaid_current;
            $sales->IsPartialPaid=$partialPaid_current;
            $sales->createdDate=date('Y-m-d h:i:s');
            $sales->isActive=1;
            $sales->user_id = $user_id;
            $sales->company_id = $company_id;
            $sales->save();
            $sales_id = $sales->id;

            $sale_details=json_decode($_POST['sale_details']);

            foreach ($sale_details as $sale_item)
            {
                $pad_number=$sale_item->PadNumber;
                $data=SaleDetail::create([
                    'sale_id'=>$sales_id,
                    'PadNumber'=>$sale_item->PadNumber,
                    'vehicle_id'=>$sale_item->vehicle_id,
                    'product_id'=>$sale_item->product_id,
                    'unit_id'=>$sale_item->unit_id,
                    'Price'=>$sale_item->Price,
                    'Quantity'=>$sale_item->Quantity,
                    'rowTotal'=>$sale_item->rowTotal,
                    'VAT'=>$sale_item->VAT,
                    'rowVatAmount'=>$sale_item->rowVatAmount,
                    'rowSubTotal'=>$sale_item->rowSubTotal,
                    'Description'=>$sale_item->Description,
                    'user_id'=>$user_id,
                    'company_id'=>$company_id,
                    'customer_id'=>$request->customer_id,
                ]);
            }

            if($request->paidBalance != 0.00 || $request->paidBalance != 0)
            {
                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                $difference = $cashTransaction->last()->Differentiate;
                $cash_transaction = new CashTransaction();
                $cash_transaction->Reference=$sales_id;
                $cash_transaction->createdDate=$request->SaleDate;
                $cash_transaction->Type='sales';
                $cash_transaction->Details='CashSales|'.$sales_id;
                $cash_transaction->Credit=0.00;
                $cash_transaction->Debit=$request->paidBalance;
                $cash_transaction->Differentiate=$difference+$request->paidBalance;
                $cash_transaction->user_id = $user_id;
                $cash_transaction->company_id = $company_id;
                $cash_transaction->PadNumber = $pad_number;
                $cash_transaction->save();
            }

            ////////////////// start account section gautam ////////////////
            if($sales_id)
            {
                $accountTransaction = AccountTransaction::where(['customer_id'=> $request->customer_id,])->get();
                // totally credit
                if ($request->paidBalance == 0 || $request->paidBalance == 0.00) {
                    $totalCredit = $request->grandTotal;
                    $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;
                    $AccData =
                        [
                            'customer_id' => $request->customer_id,
                            'Credit' => 0.00,
                            'Debit' => $totalCredit,
                            'Differentiate' => $difference,
                            'createdDate' => $request->SaleDate,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'Sales|'.$sales_id,
                            'referenceNumber'=>'P#'.$pad_number,
                        ];
                    $AccountTransactions = AccountTransaction::Create($AccData);
                }
                // partial payment some cash some credit
                elseif($request->paidBalance > 0 AND $request->paidBalance < $request->grandTotal)
                {
                    $differenceValue = $accountTransaction->last()->Differentiate - $request->paidBalance;
                    $totalCredit = $accountTransaction->last()->Differentiate + $request->grandTotal;
                    $difference = $differenceValue + $request->grandTotal;

                    //make debit entry for the sales
                    $AccData =
                        [
                            'customer_id' => $request->customer_id,
                            'Credit' => 0.00,
                            'Debit' => $request->grandTotal,
                            'Differentiate' => $totalCredit,
                            'createdDate' => $request->SaleDate,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'Sales|'.$sales_id,
                            'referenceNumber'=>'P#'.$pad_number,
                        ];
                    $AccountTransactions = AccountTransaction::Create($AccData);

                    //make credit entry for the whatever cash is paid
                    $difference=$totalCredit-$request->paidBalance;
                    $AccData =
                        [
                            'customer_id' => $request->customer_id,
                            'Credit' => $request->paidBalance,
                            'Debit' => 0.00,
                            'Differentiate' => $difference,
                            'createdDate' => $request->SaleDate,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'PartialCashSales|'.$sales_id,
                            'referenceNumber'=>'P#'.$pad_number,
                        ];
                    $AccountTransactions = AccountTransaction::Create($AccData);
                }
                // fully paid with cash
                else
                {
                    $totalCredit = $request->grandTotal;
                    $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;

                    //make credit entry for the sales
                    $AccountTransactions=AccountTransaction::Create([
                        'customer_id' => $request->customer_id,
                        'Credit' => 0.00,
                        'Debit' => $totalCredit,
                        'Differentiate' => $difference,
                        'createdDate' => $request->SaleDate,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description'=>'Sales|'.$sales_id,
                        'referenceNumber'=>'P#'.$pad_number,
                    ]);

                    //make credit entry for the whatever cash is paid
                    $difference=$difference-$request->paidBalance;
                    $AccountTransactions=AccountTransaction::Create([
                        'customer_id' => $request->customer_id,
                        'Credit' => $request->paidBalance,
                        'Debit' => 0.00,
                        'Differentiate' => $difference,
                        'createdDate' => $request->SaleDate,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description'=>'FullCashSales|'.$sales_id,
                        'referenceNumber'=>'P#'.$pad_number,
                    ]);
                }
            }
            ////////////////// end account section gautam ////////////////
        });
        $Response = SalesResource::collection(Sale::where('id',$sales_id)->with(['user','customer','sale_details'])->get());
        $data = json_decode(json_encode($Response), true);
        return $data[0];
        ////////////end new code /////////////////////////////
    }

    public function update(Request $request, $Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;

        // start new code ////////
        DB::transaction(function () use($request,$Id){

            $sold = Sale::with('customer.account_transaction')->find($Id);
            $user_id = Auth::id();
            $company_id = Str::getCompany($user_id);
            $sale_detail=json_decode($_POST['sale_details']);
            $PadNumber=$sale_detail[0]->PadNumber;

            ////////////////// account section gautam ////////////////
            $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
            if (!is_null($accountTransaction))
            {
                // payment is done (in any way - advance or payment)
                if($sold->IsPaid==1 && $sold->IsPartialPaid==0)
                {
                    //check if only customer is changed and not quantity or price = grand total is same as previous
                    if($request->customer_id!=$sold->customer_id  AND $sold->grandTotal==$request->grandTotal)
                    {
                        //supplier is changed need to reverse all previously made account entries for the previous supplier
                        // start reverse entry for wrong supplier
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
                                'createdDate' => $request->SaleDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$Id,
                                'updateDescription'=>'hide',
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // also hide previous entry start
                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                        // also hide previous entry end
                        // reverse entry done for wrong supplier

                        //start if entry is FullCashSales or PartialCashSales then need to also reverse the cash account entries
                        $description_string1='FullCashSales|'.$Id;
                        $description_string2='PartialCashSales|'.$Id;
                        $previous_probable_cash_entry = AccountTransaction::where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string1)->orWhere('Description','like',$description_string2)->get()->last();

                        if($previous_probable_cash_entry)
                        {
                            $previously_credited = $previous_probable_cash_entry->Credit;
                            $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                            $last_closing=$accountTransaction->last()->Differentiate;
                            if($previous_probable_cash_entry->Description==$description_string1)
                            {
                                $new_desc_string=$description_string1;
                            }
                            elseif($previous_probable_cash_entry->Description==$description_string1)
                            {
                                $new_desc_string=$description_string2;
                            }
                            else
                            {
                                $new_desc_string='';
                            }
                            $AccData =
                                [
                                    'customer_id' => $sold->customer_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing+$previously_credited,
                                    'createdDate' => $request->SaleDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>$new_desc_string,
                                    'referenceNumber'=>$previous_probable_cash_entry->referenceNumber,
                                    'updateDescription'=>'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_probable_cash_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end

                            $description_string1='CashSales|'.$Id;
                            $previous_probable_cash_entry = CashTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string1)->get()->first();
                            if($previous_probable_cash_entry)
                            {
                                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                                $difference = $cashTransaction->last()->Differentiate;
                                $cash_transaction = new CashTransaction();
                                $cash_transaction->Reference=$Id;
                                $cash_transaction->createdDate=$request->SaleDate;
                                $cash_transaction->Type='sales';
                                $cash_transaction->Details='CashSalesReversal|'.$Id;
                                $cash_transaction->Credit=$previously_credited;
                                $cash_transaction->Debit=0.00;
                                $cash_transaction->Differentiate=$difference-$previously_credited;
                                $cash_transaction->user_id = $user_id;
                                $cash_transaction->company_id = $company_id;
                                $cash_transaction->PadNumber = $previous_probable_cash_entry->PadNumber;
                                $cash_transaction->save();
                            }

                            // if cash paid is same need to make new cash entry here
                        }
                        //end if entry is FullCashSales or PartialCashSales then need to also reverse the cash account entries

                        if($request->paidBalance!=0)
                        {
                            $cashTransaction = CashTransaction::where(['company_id'=> $company_id,])->get();
                            if($cashTransaction)
                            {
                                $difference = $cashTransaction->last()->Differentiate;
                                $cash_transaction = new CashTransaction();
                                $cash_transaction->Reference=$Id;
                                $cash_transaction->createdDate=$request->SaleDate;
                                $cash_transaction->Type='sales';
                                $cash_transaction->Details='CashSales|'.$Id;
                                $cash_transaction->Credit=0.00;
                                $cash_transaction->Debit=$request->paidBalance;
                                $cash_transaction->Differentiate=$difference+$request->paidBalance;
                                $cash_transaction->user_id = $user_id;
                                $cash_transaction->company_id = $company_id;
                                $cash_transaction->PadNumber = $previous_probable_cash_entry->PadNumber;
                                $cash_transaction->save();
                            }
                        }

                        /*new entry*/
                        // start new entry for right supplier and credit or debit account based on closing balance
                        $accountTransaction = AccountTransaction::where(['customer_id'=> $request->customer_id,])->get();
                        $totalDebit = $request->grandTotal;
                        $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;
                        $AccData =
                            [
                                'customer_id' => $request->customer_id,
                                'Credit' => 0.00,
                                'Debit' => $totalDebit,
                                'Differentiate' => $difference,
                                'createdDate' => $request->SaleDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$Id,
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        if($difference<0)
                        {
                            // still there is advance amount so make it fully paid
                            $this_sales = Sale::find($sold->id);
                            $this_sales->update([
                                "paidBalance"        => $request->grandTotal,
                                "remainingBalance"   => 0.00,
                                "IsPaid" => 1,
                                "IsPartialPaid" => 0,
                                "IsNeedStampOrSignature" => false,
                                "Description" => 'AutoPaid',
                            ]);
                        }
                        elseif($difference>0)
                        {
                            if($difference==($request->grandTotal))
                            {
                                // now we are receivable so differance amount will be paid amount and make it partial paid
                                $this_sales = Sale::find($sold->id);
                                $this_sales->update([
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
                                // now customer is payable so
                                $this_sales = Sale::find($sold->id);
                                $this_sales->update([
                                    "paidBalance"        => 0,
                                    "remainingBalance"   => $request->grandTotal,
                                    "IsPaid" => 0,
                                    "IsPartialPaid" => 0,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => '',
                                    'referenceNumber'=>$accountTransaction->last()->referenceNumber,
                                ]);
                            }
                        }
                        /*new entry*/
                    }

                    // check if only grand total is changed and not the customer
                    elseif($request->customer_id==$sold->customer_id  AND $sold->grandTotal!=$request->grandTotal)
                    {
                        // 1 : reverse older entry
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
                                'createdDate' => $request->SaleDate,
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

                        //start if entry is FullCashSales or PartialCashSales then need to also reverse the cash account entries
                        $description_string1='FullCashSales|'.$Id;
                        $description_string2='PartialCashSales|'.$Id;
                        $previous_probable_cash_entry = AccountTransaction::where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string1)->orWhere('Description','like',$description_string2)->get()->last();

                        $cash_flag=0;
                        if($previous_probable_cash_entry)
                        {
                            $previously_credited = $previous_probable_cash_entry->Credit;
                            $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                            $last_closing=$accountTransaction->last()->Differentiate;
                            if($previous_probable_cash_entry->Description==$description_string1)
                            {
                                $new_desc_string=$description_string1;
                            }
                            elseif($previous_probable_cash_entry->Description==$description_string1)
                            {
                                $new_desc_string=$description_string2;
                            }
                            else
                            {
                                $new_desc_string='';
                            }
                            $AccData =
                                [
                                    'customer_id' => $sold->customer_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing+$previously_credited,
                                    'createdDate' => $request->SaleDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>$new_desc_string,
                                    'referenceNumber'=>$previous_probable_cash_entry->referenceNumber,
                                    'updateDescription'=>'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_probable_cash_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            $cash_flag=1;

                            $description_string1='CashSales|'.$Id;
                            $previous_probable_cash_entry = CashTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string1)->get()->first();
                            if($previous_probable_cash_entry)
                            {
                                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                                $difference = $cashTransaction->last()->Differentiate;
                                $cash_transaction = new CashTransaction();
                                $cash_transaction->Reference=$Id;
                                $cash_transaction->createdDate=$request->SaleDate;
                                $cash_transaction->Type='sales';
                                $cash_transaction->Details='CashSalesReversal|'.$Id;
                                $cash_transaction->Credit=$previously_credited;
                                $cash_transaction->Debit=0.00;
                                $cash_transaction->Differentiate=$difference-$previously_credited;
                                $cash_transaction->user_id = $user_id;
                                $cash_transaction->company_id = $company_id;
                                $cash_transaction->PadNumber = $previous_probable_cash_entry->PadNumber;
                                $cash_transaction->save();
                            }
                            // if cash paid is same need to make new cash entry here
                        }
                        //end if entry is FullCashSales or PartialCashSales then need to also reverse the cash account entries

                        /* new entry start */
                        // make new entry then check account balance
                        $accountTransaction = AccountTransaction::where(['customer_id'=> $request->customer_id,])->get();
                        $totalDebit = $request->grandTotal;
                        $difference = $accountTransaction->last()->Differentiate - $request->grandTotal;
                        $AccData =
                            [
                                'customer_id' => $request->customer_id,
                                'Credit' => 0.00,
                                'Debit' => $totalDebit,
                                'Differentiate' => $difference,
                                'createdDate' => $request->SaleDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$Id,
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        $accountTransaction_ref=$AccountTransactions->id;
                        /* new entry end */

                        if($request->paidBalance!=0)
                        {
                            $cashTransaction = CashTransaction::where(['company_id'=> $company_id,])->get();
                            if($cashTransaction)
                            {
                                $difference = $cashTransaction->last()->Differentiate;
                                $cash_transaction = new CashTransaction();
                                $cash_transaction->Reference=$Id;
                                $cash_transaction->createdDate=$request->SaleDate;
                                $cash_transaction->Type='sales';
                                $cash_transaction->Details='CashSales|'.$Id;
                                $cash_transaction->Credit=0.00;
                                $cash_transaction->Debit=$request->paidBalance;
                                $cash_transaction->Differentiate=$difference+$request->paidBalance;
                                $cash_transaction->user_id = $user_id;
                                $cash_transaction->company_id = $company_id;
                                $cash_transaction->PadNumber = $previous_probable_cash_entry->PadNumber;
                                $cash_transaction->save();

                                $accountTransaction = AccountTransaction::where(['customer_id'=> $request->customer_id,])->get();
                                $last_closing = $accountTransaction->last()->Differentiate;
                                $AccData =
                                    [
                                        'customer_id' => $request->customer_id,
                                        'Credit' => $request->paidBalance,
                                        'Debit' => 0.00,
                                        'Differentiate' => $last_closing+$request->paidBalance,
                                        'createdDate' => $request->SaleDate,
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'FullCashSales|'.$Id,
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                            }
                        }

                        // if difference is positive meaning advance is over and we are receivable
                        // so update sales entry with difference amount as received amount
                        if($cash_flag)
                        {
                            if($difference<0)
                            {
                                // still there is advance amount so make it fully paid
                                $this_sales = Sale::find($sold->id);
                                $this_sales->update([
                                    "paidBalance"        => $request->grandTotal,
                                    "remainingBalance"   => 0.00,
                                    "IsPaid" => 1,
                                    "IsPartialPaid" => 0,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid1',
                                ]);
                            }
                            elseif($difference>0)
                            {
                                if($request->paidBalance==($request->grandTotal))
                                {
                                    $this_sales = Sale::find($sold->id);
                                    $this_sales->update([
                                        "paidBalance"        => $request->grandTotal,
                                        "remainingBalance"   => 0,
                                        "IsPaid" => 1,
                                        "IsPartialPaid" => 0,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'CP1',
                                    ]);
                                }
                                else
                                {
                                    $this_sales = Sale::find($sold->id);
                                    $this_sales->update([
                                        "paidBalance"        => $request->paidBalance,
                                        "remainingBalance"   => $request->grandTotal-$request->paidBalance,
                                        "IsPaid" => 0,
                                        "IsPartialPaid" => 1,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'CP2',
                                    ]);
                                }
                            }
                        }
                        else
                        {
                            if($difference==$request->grandTotal)
                            {
                                // now we are payable so differance amount will be paid amount and make it partial paid
                                $this_sales = Sale::find($sold->id);
                                $this_sales->update([
                                    "paidBalance"        => 0,
                                    "remainingBalance"   => $difference,
                                    "IsPaid" => 0,
                                    "IsPartialPaid" => 0,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid2',
                                ]);
                            }
                            else
                            {
                                // now we are payable so differance amount will be paid amount and make it partial paid
                                $this_sales = Sale::find($sold->id);
                                $this_sales->update([
                                    "paidBalance"        => $request->grandTotal-$difference,
                                    "remainingBalance"   => $difference,
                                    "IsPaid" => 0,
                                    "IsPartialPaid" => 1,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid3',
                                ]);
                            }
                        }

                        // fully paid case will come here
                    }

                    // check both customer and grandTotal is changed meaning case 3
                    elseif($request->customer_id!=$sold->customer_id  AND $sold->grandTotal!=$request->grandTotal)
                    {
                        // start reverse entry for wrong customer with wrong entries
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
                                'createdDate' => $request->SaleDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$Id,
                                'referenceNumber'=>$previous_entry->referenceNumber,
                                'updateDescription'=>'hide',
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // also hide previous entry start
                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                        // also hide previous entry end
                        // reverse entry done for wrong customer

                        //start if entry is FullCashSales or PartialCashSales then need to also reverse the cash account entries
                        $description_string1='FullCashSales|'.$Id;
                        $description_string2='PartialCashSales|'.$Id;
                        $previous_probable_cash_entry = AccountTransaction::where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string1)->orWhere('Description','like',$description_string2)->get()->last();

                        if($previous_probable_cash_entry)
                        {
                            $previously_credited = $previous_probable_cash_entry->Credit;
                            $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                            $last_closing=$accountTransaction->last()->Differentiate;
                            if($previous_probable_cash_entry->Description==$description_string1)
                            {
                                $new_desc_string=$description_string1;
                            }
                            elseif($previous_probable_cash_entry->Description==$description_string1)
                            {
                                $new_desc_string=$description_string2;
                            }
                            else
                            {
                                $new_desc_string='';
                            }
                            $AccData =
                                [
                                    'customer_id' => $sold->customer_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing+$previously_credited,
                                    'createdDate' => $request->SaleDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>$new_desc_string,
                                    'referenceNumber'=>$previous_probable_cash_entry->referenceNumber,
                                    'updateDescription'=>'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_probable_cash_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end

                            $description_string1='CashSales|'.$Id;
                            $previous_probable_cash_entry = CashTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string1)->get()->first();
                            if($previous_probable_cash_entry)
                            {
                                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                                $difference = $cashTransaction->last()->Differentiate;
                                $cash_transaction = new CashTransaction();
                                $cash_transaction->Reference=$Id;
                                $cash_transaction->createdDate=$request->SaleDate;
                                $cash_transaction->Type='sales';
                                $cash_transaction->Details='CashSalesReversal|'.$Id;
                                $cash_transaction->Credit=$previously_credited;
                                $cash_transaction->Debit=0.00;
                                $cash_transaction->Differentiate=$difference-$previously_credited;
                                $cash_transaction->user_id = $user_id;
                                $cash_transaction->company_id = $company_id;
                                $cash_transaction->PadNumber = $previous_probable_cash_entry->PadNumber;
                                $cash_transaction->save();
                            }

                            // if cash paid is same need to make new cash entry here
                        }
                        //end if entry is FullCashSales or PartialCashSales then need to also reverse the cash account entries

                        if($request->paidBalance!=0)
                        {
                            $cashTransaction = CashTransaction::where(['company_id'=> $company_id,])->get();
                            if($cashTransaction)
                            {
                                $difference = $cashTransaction->last()->Differentiate;
                                $cash_transaction = new CashTransaction();
                                $cash_transaction->Reference=$Id;
                                $cash_transaction->createdDate=$request->SaleDate;
                                $cash_transaction->Type='sales';
                                $cash_transaction->Details='CashSales|'.$Id;
                                $cash_transaction->Credit=0.00;
                                $cash_transaction->Debit=$request->paidBalance;
                                $cash_transaction->Differentiate=$difference+$request->paidBalance;
                                $cash_transaction->user_id = $user_id;
                                $cash_transaction->company_id = $company_id;
                                $cash_transaction->PadNumber = $previous_probable_cash_entry->PadNumber;
                                $cash_transaction->save();
                            }
                        }

                        /*new entry with right grand total */
                        // start new entry for right customer and credit or debit account based on closing balance
                        $accountTransaction = AccountTransaction::where(['customer_id'=> $request->customer_id,])->get();
                        $totalDebit = $request->grandTotal;
                        $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;
                        $AccData =
                            [
                                'customer_id' => $request->customer_id,
                                'Credit' => 0.00,
                                'Debit' => $totalDebit,
                                'Differentiate' => $difference,
                                'createdDate' => $request->SaleDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$Id,
                                'referenceNumber'=>$accountTransaction->last()->referenceNumber,
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // check if new incoming entry is credit or not
                        if($request->paidBalance==0)
                        {
                            $this_sales = Sale::find($sold->id);
                            $this_sales->update([
                                "paidBalance"        => 0,
                                "remainingBalance"   => $request->grandTotal,
                                "IsPaid" => 0,
                                "IsPartialPaid" => 0,
                                "IsNeedStampOrSignature" => false,
                                "Description" => '',
                            ]);
                        }
                        else
                        {
                            if($difference<0)
                            {
                                // still there is advance amount so make it fully paid
                                $this_sales = Sale::find($sold->id);
                                $this_sales->update([
                                    "paidBalance"        => $request->grandTotal,
                                    "remainingBalance"   => 0.00,
                                    "IsPaid" => 1,
                                    "IsPartialPaid" => 0,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid',
                                ]);
                            }
                            elseif($difference>0)
                            {
                                if($difference==($request->grandTotal))
                                {
                                    // now we are payable so differance amount will be paid amount and make it partial paid
                                    $this_sales = Sale::find($sold->id);
                                    $this_sales->update([
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
                                    $this_sales = Sale::find($sold->id);
                                    $this_sales->update([
                                        "paidBalance"        => 0,
                                        "remainingBalance"   => $request->grandTotal,
                                        "IsPaid" => 0,
                                        "IsPartialPaid" => 0,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid',
                                    ]);
                                }
                            }
                        }

                        /*new entry with right grand total*/
                    }

                    // check only cash is reduction in payment nothing else
                    elseif ($request->paidBalance!=$sold->paidBalance)
                    {
                        // 1 : reverse older entry
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
                                'createdDate' => $request->SaleDate,
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

                        //start if entry is FullCashSales or PartialCashSales then need to also reverse the cash account entries
                        $description_string1='FullCashSales|'.$Id;
                        $description_string2='PartialCashSales|'.$Id;
                        $previous_probable_cash_entry = AccountTransaction::where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string1)->orWhere('Description','like',$description_string2)->get()->last();

                        $cash_flag=0;
                        if($previous_probable_cash_entry)
                        {
                            $previously_credited = $previous_probable_cash_entry->Credit;
                            $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                            $last_closing=$accountTransaction->last()->Differentiate;
                            if($previous_probable_cash_entry->Description==$description_string1)
                            {
                                $new_desc_string=$description_string1;
                            }
                            elseif($previous_probable_cash_entry->Description==$description_string1)
                            {
                                $new_desc_string=$description_string2;
                            }
                            else
                            {
                                $new_desc_string='';
                            }
                            $AccData =
                                [
                                    'customer_id' => $sold->customer_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing+$previously_credited,
                                    'createdDate' => $request->SaleDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>$new_desc_string,
                                    'referenceNumber'=>$previous_probable_cash_entry->referenceNumber,
                                    'updateDescription'=>'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_probable_cash_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            $cash_flag=1;

                            $description_string1='CashSales|'.$Id;
                            $previous_probable_cash_entry = CashTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string1)->get()->first();
                            if($previous_probable_cash_entry)
                            {
                                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                                $difference = $cashTransaction->last()->Differentiate;
                                $cash_transaction = new CashTransaction();
                                $cash_transaction->Reference=$Id;
                                $cash_transaction->createdDate=$request->SaleDate;
                                $cash_transaction->Type='sales';
                                $cash_transaction->Details='CashSalesReversal|'.$Id;
                                $cash_transaction->Credit=$previously_credited;
                                $cash_transaction->Debit=0.00;
                                $cash_transaction->Differentiate=$difference-$previously_credited;
                                $cash_transaction->user_id = $user_id;
                                $cash_transaction->company_id = $company_id;
                                $cash_transaction->PadNumber = $previous_probable_cash_entry->PadNumber;
                                $cash_transaction->save();
                            }
                            // if cash paid is same need to make new cash entry here
                        }
                        //end if entry is FullCashSales or PartialCashSales then need to also reverse the cash account entries

                        /* new entry start */
                        // make new entry then check account balance
                        $accountTransaction = AccountTransaction::where(['customer_id'=> $request->customer_id,])->get();
                        $totalDebit = $request->grandTotal;
                        $difference = $accountTransaction->last()->Differentiate - $request->grandTotal;
                        $AccData =
                            [
                                'customer_id' => $request->customer_id,
                                'Credit' => 0.00,
                                'Debit' => $totalDebit,
                                'Differentiate' => $difference,
                                'createdDate' => $request->SaleDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$Id,
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        $accountTransaction_ref=$AccountTransactions->id;
                        /* new entry end */

                        if($request->paidBalance!=0)
                        {
                            $cashTransaction = CashTransaction::where(['company_id'=> $company_id,])->get();
                            if($cashTransaction)
                            {
                                $difference = $cashTransaction->last()->Differentiate;
                                $cash_transaction = new CashTransaction();
                                $cash_transaction->Reference=$Id;
                                $cash_transaction->createdDate=$request->SaleDate;
                                $cash_transaction->Type='sales';
                                $cash_transaction->Details='CashSales|'.$Id;
                                $cash_transaction->Credit=0.00;
                                $cash_transaction->Debit=$request->paidBalance;
                                $cash_transaction->Differentiate=$difference+$request->paidBalance;
                                $cash_transaction->user_id = $user_id;
                                $cash_transaction->company_id = $company_id;
                                $cash_transaction->PadNumber = $previous_probable_cash_entry->PadNumber;
                                $cash_transaction->save();

                                $accountTransaction = AccountTransaction::where(['customer_id'=> $request->customer_id,])->get();
                                $last_closing = $accountTransaction->last()->Differentiate;
                                $AccData =
                                    [
                                        'customer_id' => $request->customer_id,
                                        'Credit' => $request->paidBalance,
                                        'Debit' => 0.00,
                                        'Differentiate' => $last_closing+$request->paidBalance,
                                        'createdDate' => $request->SaleDate,
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'FullCashSales|'.$Id,
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                            }
                        }

                        // if difference is positive meaning advance is over and we are receivable
                        // so update sales entry with difference amount as received amount
                        if($cash_flag)
                        {
                            if($difference<0)
                            {
                                // still there is advance amount so make it fully paid
                                $this_sales = Sale::find($sold->id);
                                $this_sales->update([
                                    "paidBalance"        => $request->grandTotal,
                                    "remainingBalance"   => 0.00,
                                    "IsPaid" => 1,
                                    "IsPartialPaid" => 0,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid1',
                                ]);
                            }
                            elseif($difference>0)
                            {
                                if($request->paidBalance==($request->grandTotal))
                                {
                                    $this_sales = Sale::find($sold->id);
                                    $this_sales->update([
                                        "paidBalance"        => $request->grandTotal,
                                        "remainingBalance"   => 0,
                                        "IsPaid" => 1,
                                        "IsPartialPaid" => 0,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'CP1',
                                    ]);
                                }
                                else
                                {
                                    $this_sales = Sale::find($sold->id);
                                    $this_sales->update([
                                        "paidBalance"        => $request->paidBalance,
                                        "remainingBalance"   => $request->grandTotal-$request->paidBalance,
                                        "IsPaid" => 0,
                                        "IsPartialPaid" => 1,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'CP2',
                                    ]);
                                }
                            }
                        }
                        else
                        {
                            if($difference==$request->grandTotal)
                            {
                                // now we are payable so differance amount will be paid amount and make it partial paid
                                $this_sales = Sale::find($sold->id);
                                $this_sales->update([
                                    "paidBalance"        => 0,
                                    "remainingBalance"   => $difference,
                                    "IsPaid" => 0,
                                    "IsPartialPaid" => 0,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid2',
                                ]);
                            }
                            else
                            {
                                // now we are payable so differance amount will be paid amount and make it partial paid
                                $this_sales = Sale::find($sold->id);
                                $this_sales->update([
                                    "paidBalance"        => $request->grandTotal-$difference,
                                    "remainingBalance"   => $difference,
                                    "IsPaid" => 0,
                                    "IsPartialPaid" => 1,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid3',
                                ]);
                            }
                        }
                    }
                }
                elseif($sold->IsPaid==0 && $sold->IsPartialPaid==1)
                {
                    // if more cash incoming then need to add in customer account
                    if($sold->paidBalance!=$request->paidBalance)
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
                            $cash_transaction->createdDate=$request->SaleDate;
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
                            $cash_transaction->createdDate=$request->SaleDate;
                            $cash_transaction->Type='sales';
                            $cash_transaction->Details='CashSales|'.$Id;
                            $cash_transaction->Credit=0.00;
                            $cash_transaction->Debit=$request->paidBalance;
                            $cash_transaction->Differentiate=$difference+$request->paidBalance;
                            $cash_transaction->user_id = $user_id;
                            $cash_transaction->company_id = $company_id;
                            $cash_transaction->save();
                            // end new entry

                            // now here we check if only and only cash paid is updating and none of the below case will execute then we need..
                            // to check if there any existing entry with PartialCashSales|$id and not hidden we need to reverse that entry
                            if($request->customer_id==$sold->customer_id  AND $sold->grandTotal==$request->grandTotal)
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
                                            'createdDate' => $request->SaleDate,
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
                                            'Credit' => $request->paidBalance,
                                            'Differentiate' => $last_closing-$request->paidBalance,
                                            'createdDate' => $request->SaleDate,
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
                            $cash_transaction->createdDate=$request->SaleDate;
                            $cash_transaction->Type='sales';
                            $cash_transaction->Details='CashSales|'.$Id;
                            $cash_transaction->Credit=0.00;
                            $cash_transaction->Debit=$request->paidBalance;
                            $cash_transaction->Differentiate=$difference+$request->paidBalance;
                            $cash_transaction->user_id = $user_id;
                            $cash_transaction->company_id = $company_id;
                            $cash_transaction->PadNumber = $PadNumber;
                            $cash_transaction->save();
                            // end new entry

                            // now here we check if only and only cash paid is updating and none of the below case will execute then we need..
                            // to create one more cash entry for this sales as partial cash sales entry in account transaction
                            if($request->customer_id==$sold->customer_id  AND $sold->grandTotal==$request->grandTotal)
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
                                        'Credit' => $request->paidBalance,
                                        'Differentiate' => $last_closing-$request->paidBalance,
                                        'createdDate' => $request->SaleDate,
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'PartialCashSales|'.$Id,
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                            }
                        }

                        if($request->grandTotal==$request->paidBalance)
                        {
                            $sold->IsPaid=1;
                            $sold->IsPartialPaid=0;
                            $sold->paidBalance=$request->paidBalance;
                            $sold->remainingBalance=$request->grandTotal-$request->paidBalance;
                        }
                        elseif($request->paidBalance<$sold->grandTotal)
                        {
                            $sold->IsPaid=0;
                            $sold->IsPartialPaid=1;
                            $sold->paidBalance=$request->paidBalance;
                            $sold->remainingBalance=$request->grandTotal-$request->paidBalance;
                        }
                    }

                    //check if only customer is changed and not quantity or price = grand total is same as previous
                    if($request->customer_id!=$sold->customer_id  AND $sold->grandTotal==$request->grandTotal)
                    {
                        //supplier is changed need to reverse all previously made account entries for the previous supplier

                        // start reverse entry for wrong supplier
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
                                'createdDate' => $request->SaleDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$Id,
                                'updateDescription'=>'hide',
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // also hide previous entry start
                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                        // also hide previous entry end
                        // reverse entry done for wrong supplier

                        /*new entry*/
                        // start new entry for right supplier and credit or debit account based on closing balance
                        $accountTransaction = AccountTransaction::where(['customer_id'=> $request->customer_id,])->get();
                        $totalDebit = $request->grandTotal;
                        $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;
                        $AccData =
                            [
                                'customer_id' => $request->customer_id,
                                'Credit' => 0.00,
                                'Debit' => $totalDebit,
                                'Differentiate' => $difference,
                                'createdDate' => $request->SaleDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$Id,
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        if($difference<0)
                        {
                            // still there is advance amount so make it fully paid
                            $this_sales = Sale::find($sold->id);
                            $this_sales->update([
                                "paidBalance"        => $request->grandTotal,
                                "remainingBalance"   => 0.00,
                                "IsPaid" => 1,
                                "IsPartialPaid" => 0,
                                "IsNeedStampOrSignature" => false,
                                "Description" => 'AutoPaid',
                            ]);
                        }
                        elseif($difference>0)
                        {
                            if($difference==($request->grandTotal))
                            {
                                // now we are receivable so differance amount will be paid amount and make it partial paid
                                $this_sales = Sale::find($sold->id);
                                $this_sales->update([
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
                                // now we are receivable so differance amount will be paid amount and make it partial paid
                                $this_sales = Sale::find($sold->id);
                                $this_sales->update([
                                    "paidBalance"        => $request->grandTotal-$difference,
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

                    // check if only grand total is changed and not the customer
                    elseif($request->customer_id==$sold->customer_id  AND $sold->grandTotal!=$request->grandTotal)
                    {
                        // 1 : reverse older entry
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
                                'createdDate' => $request->SaleDate,
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

                        /* new entry start */
                        // make new entry then check account balance
                        $accountTransaction = AccountTransaction::where(['customer_id'=> $request->customer_id,])->get();

                        $totalDebit = $request->grandTotal;
                        $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;
                        $AccData =
                            [
                                'customer_id' => $request->customer_id,
                                'Credit' => 0.00,
                                'Debit' => $totalDebit,
                                'Differentiate' => $difference,
                                'createdDate' => $request->SaleDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$Id,
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        $accountTransaction_ref=$AccountTransactions->id;
                        /* new entry end */

                        // if difference is positive meaning advance is over and we are receivable
                        // so update sales entry with difference amount as received amount
                        if($difference<0)
                        {
                            // still there is advance amount so make it fully paid
                            $this_sales = Sale::find($sold->id);
                            $this_sales->update([
                                "paidBalance"        => $request->grandTotal,
                                "remainingBalance"   => 0.00,
                                "IsPaid" => 1,
                                "IsPartialPaid" => 0,
                                "IsNeedStampOrSignature" => false,
                                "Description" => 'AutoPaid',
                            ]);
                        }
                        elseif($difference>0)
                        {
                            if($difference==($request->grandTotal))
                            {
                                // now we are payable so differance amount will be paid amount and make it partial paid
                                $this_sales = Sale::find($sold->id);
                                $this_sales->update([
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
                                $this_sales = Sale::find($sold->id);
                                $this_sales->update([
                                    "paidBalance"        => $request->grandTotal-$difference,
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

                    // check both customer and grandTotal is changed meaning case 3
                    elseif($request->customer_id!=$sold->customer_id  AND $sold->grandTotal!=$request->grandTotal)
                    {
                        // start reverse entry for wrong customer with wrong entries
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
                                'createdDate' => $request->SaleDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$Id,
                                'updateDescription'=>'hide',
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // also hide previous entry start
                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                        // also hide previous entry end
                        // reverse entry done for wrong customer

                        //start if entry is FullCashSales or PartialCashSales then need to also reverse the cash account entries
                        $description_string1='FullCashSales|'.$Id;
                        $description_string2='PartialCashSales|'.$Id;
                        $previous_probable_cash_entry = AccountTransaction::where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string1)->orWhere('Description','like',$description_string2)->get()->last();

                        if($previous_probable_cash_entry)
                        {
                            $previously_credited = $previous_probable_cash_entry->Credit;
                            $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                            $last_closing=$accountTransaction->last()->Differentiate;
                            if($previous_probable_cash_entry->Description==$description_string1)
                            {
                                $new_desc_string=$description_string1;
                            }
                            elseif($previous_probable_cash_entry->Description==$description_string1)
                            {
                                $new_desc_string=$description_string2;
                            }
                            else
                            {
                                $new_desc_string='';
                            }
                            $AccData =
                                [
                                    'customer_id' => $sold->customer_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing+$previously_credited,
                                    'createdDate' => $request->SaleDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>$new_desc_string,
                                    'referenceNumber'=>$previous_probable_cash_entry->referenceNumber,
                                    'updateDescription'=>'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_probable_cash_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end

                            $description_string1='CashSales|'.$Id;
                            $previous_probable_cash_entry = CashTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string1)->get()->first();
                            if($previous_probable_cash_entry)
                            {
                                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                                $difference = $cashTransaction->last()->Differentiate;
                                $cash_transaction = new CashTransaction();
                                $cash_transaction->Reference=$Id;
                                $cash_transaction->createdDate=$request->SaleDate;
                                $cash_transaction->Type='sales';
                                $cash_transaction->Details='CashSalesReversal|'.$Id;
                                $cash_transaction->Credit=$previously_credited;
                                $cash_transaction->Debit=0.00;
                                $cash_transaction->Differentiate=$difference-$previously_credited;
                                $cash_transaction->user_id = $user_id;
                                $cash_transaction->company_id = $company_id;
                                $cash_transaction->PadNumber = $previous_probable_cash_entry->PadNumber;
                                $cash_transaction->save();
                            }

                            // if cash paid is same need to make new cash entry here
                        }
                        //end if entry is FullCashSales or PartialCashSales then need to also reverse the cash account entries

                        /*new entry with right grand total */
                        // start new entry for right customer and credit or debit account based on closing balance
                        $accountTransaction = AccountTransaction::where(['customer_id'=> $request->customer_id,])->get();
                        $totalDebit = $request->grandTotal;
                        $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;
                        $AccData =
                            [
                                'customer_id' => $request->customer_id,
                                'Credit' => 0.00,
                                'Debit' => $totalDebit,
                                'Differentiate' => $difference,
                                'createdDate' => $request->SaleDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$Id,
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);

                        if($request->paidBalance==0)
                        {
                            $this_sales = Sale::find($sold->id);
                            $this_sales->update([
                                "paidBalance"        => 0,
                                "remainingBalance"   => $request->grandTotal,
                                "IsPaid" => 0,
                                "IsPartialPaid" => 0,
                                "IsNeedStampOrSignature" => false,
                                "Description" => '',
                            ]);
                        }
                        else
                        {
                            if ($difference < 0) {
                                // still there is advance amount so make it fully paid
                                $this_sales = Sale::find($sold->id);
                                $this_sales->update([
                                    "paidBalance" => $request->grandTotal,
                                    "remainingBalance" => 0.00,
                                    "IsPaid" => 1,
                                    "IsPartialPaid" => 0,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid',
                                ]);
                            } elseif ($difference > 0) {
                                if ($difference == ($request->grandTotal)) {
                                    // now we are payable so differance amount will be paid amount and make it partial paid
                                    $this_sales = Sale::find($sold->id);
                                    $this_sales->update([
                                        "paidBalance" => 0,
                                        "remainingBalance" => $difference,
                                        "IsPaid" => 0,
                                        "IsPartialPaid" => 0,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid',
                                    ]);
                                } else {
                                    // now we are payable so differance amount will be paid amount and make it partial paid
                                    $this_sales = Sale::find($sold->id);
                                    $this_sales->update([
                                        "paidBalance" => $request->grandTotal - $difference,
                                        "remainingBalance" => $difference,
                                        "IsPaid" => 0,
                                        "IsPartialPaid" => 1,
                                        "IsNeedStampOrSignature" => false,
                                        "Description" => 'AutoPaid',
                                    ]);
                                }
                            }
                        }
                        /*new entry with right grand total*/
                    }

                    if($request->paidBalance!=$sold->paidBalance)
                    {
                        //hello
                        //meaning only paid cash is amount is changing
                    }
                }
                elseif($sold->IsPaid==0 && $sold->IsPartialPaid==0)
                {
                    // if paid balance is not same as earlier need to update cash account as well
                    if($sold->paidBalance!=$request->paidBalance)
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
                            $cash_transaction->createdDate=$request->SaleDate;
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
                            $cash_transaction->createdDate=$request->SaleDate;
                            $cash_transaction->Type='sales';
                            $cash_transaction->Details='CashSales|'.$Id;
                            $cash_transaction->Credit=0.00;
                            $cash_transaction->Debit=$request->paidBalance;
                            $cash_transaction->Differentiate=$difference+$request->paidBalance;
                            $cash_transaction->user_id = $user_id;
                            $cash_transaction->company_id = $company_id;
                            $cash_transaction->save();
                            // end new entry

                            // now here we check if only and only cash paid is updating and none of the below case will execute then we need..
                            // to check if there any existing entry with PartialCashSales|$id and not hidden we need to reverse that entry
                            if($request->customer_id==$sold->customer_id  AND $sold->grandTotal==$request->grandTotal)
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
                                            'createdDate' => $request->SaleDate,
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
                                            'Credit' => $request->paidBalance,
                                            'Differentiate' => $last_closing-$request->paidBalance,
                                            'createdDate' => $request->SaleDate,
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
                            $cash_transaction->createdDate=$request->SaleDate;
                            $cash_transaction->Type='sales';
                            $cash_transaction->Details='CashSales|'.$Id;
                            $cash_transaction->Credit=0.00;
                            $cash_transaction->Debit=$request->paidBalance;
                            $cash_transaction->Differentiate=$difference+$request->paidBalance;
                            $cash_transaction->user_id = $user_id;
                            $cash_transaction->company_id = $company_id;
                            $cash_transaction->PadNumber = $PadNumber;
                            $cash_transaction->save();
                            // end new entry

                            // now here we check if only and only cash paid is updating and none of the below case will execute then we need..
                            // to create one more cash entry for this sales as partial cash sales entry in account transaction
                            if($request->customer_id==$sold->customer_id  AND $sold->grandTotal==$request->grandTotal)
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
                                        'Credit' => $request->paidBalance,
                                        'Differentiate' => $last_closing-$request->paidBalance,
                                        'createdDate' => $request->SaleDate,
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'PartialCashSales|'.$Id,
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                            }
                        }

                        if($request->grandTotal==$request->paidBalance)
                        {
                            $sold->IsPaid=1;
                            $sold->IsPartialPaid=0;
                            $sold->paidBalance=$sold->paidBalance+$request->paidBalance;
                            $sold->remainingBalance=$sold->remainingBalance-$request->paidBalance;
                        }
                        elseif($request->paidBalance<$sold->grandTotal)
                        {
                            $sold->IsPaid=0;
                            $sold->IsPartialPaid=1;
                            $sold->paidBalance=$sold->paidBalance+$request->paidBalance;
                            $sold->remainingBalance=$sold->remainingBalance-$request->paidBalance;
                        }
                    }

                    // here will come 3 cases
                    // 1. only customer is updating - quantity and price remains same
                    // 2. only quantity or price updating - customer is the same
                    // 3. both customer and quantity or price updating

                    // 1 check if only customer is changed and not quantity or price = grand total is same as previous
                    if($request->customer_id!=$sold->customer_id  AND $sold->grandTotal==$request->grandTotal)
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
                                    'createdDate' => $request->SaleDate,
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
                                    'createdDate' => $request->SaleDate,
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
                                    'createdDate' => $request->SaleDate,
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
                                    'createdDate' => $request->SaleDate,
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
                                    'createdDate' => $request->SaleDate,
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
                        $accountTransaction = AccountTransaction::where(['customer_id'=> $request->customer_id,])->get();
                        // totally credit
                        if ($request->paidBalance == 0 || $request->paidBalance == 0.00) {
                            $totalCredit = $request->grandTotal;
                            $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;

                            $AccData =
                                [
                                    'customer_id' => $request->customer_id,
                                    'Credit' => 0.00,
                                    'Debit' => $totalCredit,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->SaleDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Sales|'.$Id,
                                    'referenceNumber'=>'P#'.$PadNumber,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                        }
                        // partial payment some cash some credit
                        elseif($request->paidBalance > 0 AND $request->paidBalance < $request->grandTotal )
                        {
                            $differenceValue = $accountTransaction->last()->Differentiate - $request->paidBalance;
                            $totalCredit = $accountTransaction->last()->Differentiate + $request->grandTotal;
                            $difference = $differenceValue + $request->grandTotal;

                            //make credit entry for the sales
                            $AccData =
                                [
                                    'customer_id' => $request->customer_id,
                                    'Credit' => 0.00,
                                    'Debit' => $request->grandTotal,
                                    'Differentiate' => $totalCredit,
                                    'createdDate' => $request->SaleDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Sales|'.$Id,
                                    'referenceNumber'=>'P#'.$PadNumber,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);

                            //make debit entry for the whatever cash is paid
                            $difference=$totalCredit-$request->paidBalance;
                            $AccData =
                                [
                                    'customer_id' => $request->customer_id,
                                    'Credit' => $request->paidBalance,
                                    'Debit' => 0.00,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->SaleDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'PartialCashSales|'.$Id,
                                    'referenceNumber'=>'PartialCashSales#'.$PadNumber,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                        }
                        // fully paid with cash
                        else
                        {
                            $totalCredit = $request->grandTotal;
                            $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;

                            //make credit entry for the sales
                            $AccountTransactions=AccountTransaction::Create([
                                'customer_id' => $request->customer_id,
                                'Credit' => 0.00,
                                'Debit' => $totalCredit,
                                'Differentiate' => $difference,
                                'createdDate' => $request->SaleDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$Id,
                                'referenceNumber'=>'P#'.$PadNumber,
                            ]);

                            //make debit entry for the whatever cash is paid
                            $difference=$difference-$request->paidBalance;
                            $AccountTransactions=AccountTransaction::Create([
                                'customer_id' => $request->customer_id,
                                'Credit' => $request->paidBalance,
                                'Debit' => 0.00,
                                'Differentiate' => $difference,
                                'createdDate' => $request->SaleDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'FullCashSales|'.$Id,
                                'referenceNumber'=>'FullCashSales#'.$PadNumber,
                            ]);
                        }
                        /*new entry*/
                    }
                    // check if only grand total is changed and not the customer
                    elseif($request->customer_id==$sold->customer_id  AND $sold->grandTotal!=$request->grandTotal)
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
                                    'createdDate' => $request->SaleDate,
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
                                    'createdDate' => $request->SaleDate,
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
                                    'createdDate' => $request->SaleDate,
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
                                    'createdDate' => $request->SaleDate,
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
                        $accountTransaction = AccountTransaction::where(['customer_id'=> $request->customer_id,])->get();
                        // totally credit
                        if ($request->paidBalance == 0 || $request->paidBalance == 0.00) {
                            $totalCredit = $request->grandTotal;
                            $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;

                            $AccData =
                                [
                                    'customer_id' => $request->customer_id,
                                    'Credit' => 0.00,
                                    'Debit' => $totalCredit,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->SaleDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Sales|'.$Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                        }
                        // partial payment some cash some credit
                        elseif($request->paidBalance > 0 AND $request->paidBalance < $request->grandTotal )
                        {
                            $differenceValue = $accountTransaction->last()->Differentiate - $request->paidBalance;
                            $totalCredit = $accountTransaction->last()->Differentiate + $request->grandTotal;
                            $difference = $differenceValue + $request->grandTotal;

                            //make credit entry for the sales
                            $AccData =
                                [
                                    'customer_id' => $request->customer_id,
                                    'Credit' => 0.00,
                                    'Debit' => $request->grandTotal,
                                    'Differentiate' => $totalCredit,
                                    'createdDate' => $request->SaleDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Sales|'.$Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);

                            //make debit entry for the whatever cash is paid
                            $difference=$totalCredit-$request->paidBalance;
                            $AccData =
                                [
                                    'customer_id' => $request->customer_id,
                                    'Credit' => $request->paidBalance,
                                    'Debit' => 0.00,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->SaleDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'PartialCashSales|'.$Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                        }
                        // fully paid with cash or there may be some advance amount remains
                        else
                        {
                            $totalCredit = $request->grandTotal;
                            $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;

                            //make credit entry for the sales
                            $AccountTransactions=AccountTransaction::Create([
                                'customer_id' => $request->customer_id,
                                'Credit' => 0.00,
                                'Debit' => $totalCredit,
                                'Differentiate' => $difference,
                                'createdDate' => $request->SaleDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$Id,
                            ]);

                            //make debit entry for the whatever cash is paid
                            $difference=$difference-$request->paidBalance;
                            $AccountTransactions=AccountTransaction::Create([
                                'customer_id' => $request->customer_id,
                                'Credit' => $request->paidBalance,
                                'Debit' => 0.00,
                                'Differentiate' => $difference,
                                'createdDate' => $request->SaleDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'FullCashSales|'.$Id,
                            ]);
                        }
                        /*new entry*/
                    }
                    // check both supplier and grandTotal is changed meaning case 3
                    elseif($request->customer_id!=$sold->customer_id  AND $sold->grandTotal!=$request->grandTotal)
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
                                    'createdDate' => $request->SaleDate,
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
                                    'createdDate' => $request->SaleDate,
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
                                    'createdDate' => $request->SaleDate,
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
                                    'createdDate' => $request->SaleDate,
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
                                    'createdDate' => $request->SaleDate,
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
                        $accountTransaction = AccountTransaction::where(['customer_id'=> $request->customer_id,])->get();
                        // totally credit
                        if ($request->paidBalance == 0 || $request->paidBalance == 0.00) {
                            $totalCredit = $request->grandTotal;
                            $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;

                            $AccData =
                                [
                                    'customer_id' => $request->customer_id,
                                    'Credit' => 0.00,
                                    'Debit' => $totalCredit,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->SaleDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Sales|'.$Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                        }
                        // partial payment some cash some credit
                        elseif($request->paidBalance > 0 AND $request->paidBalance < $request->grandTotal )
                        {
                            $differenceValue = $accountTransaction->last()->Differentiate - $request->paidBalance;
                            $totalCredit = $accountTransaction->last()->Differentiate + $request->grandTotal;
                            $difference = $differenceValue + $request->grandTotal;

                            //make credit entry for the sales
                            $AccData =
                                [
                                    'customer_id' => $request->customer_id,
                                    'Credit' => 0.00,
                                    'Debit' => $request->grandTotal,
                                    'Differentiate' => $totalCredit,
                                    'createdDate' => $request->SaleDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Sales|'.$Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);

                            //make debit entry for the whatever cash is paid
                            $difference=$totalCredit-$request->paidBalance;
                            $AccData =
                                [
                                    'customer_id' => $request->customer_id,
                                    'Credit' => $request->paidBalance,
                                    'Debit' => 0.00,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->SaleDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'PartialCashSales|'.$Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                        }
                        // fully paid with cash
                        else
                        {
                            $totalCredit = $request->grandTotal;
                            $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;

                            //make credit entry for the sales
                            $AccountTransactions=AccountTransaction::Create([
                                'customer_id' => $request->customer_id,
                                'Credit' => 0.00,
                                'Debit' => $totalCredit,
                                'Differentiate' => $difference,
                                'createdDate' => $request->SaleDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$Id,
                            ]);

                            //make debit entry for the whatever cash is paid
                            $difference=$difference-$request->paidBalance;
                            $AccountTransactions=AccountTransaction::Create([
                                'customer_id' => $request->customer_id,
                                'Credit' => $request->paidBalance,
                                'Debit' => 0.00,
                                'Differentiate' => $difference,
                                'createdDate' => $request->SaleDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'FullCashSales|'.$Id,
                            ]);
                        }
                        /*new entry*/
                    }
                }
                //return Response()->json($accountTransaction);
            }
            ////////////////// end of account section gautam ////////////////

            if($sold->IsPaid==1 && $sold->IsPartialPaid==0)
            {
                $sold->customer_id=$request->customer_id;
                $sold->SaleDate=$request->SaleDate;
                $sold->Total=$request->Total;
                $sold->subTotal=$request->subTotal;
                $sold->totalVat=$request->totalVat;
                $sold->grandTotal=$request->grandTotal;
                $sold->supplierNote=$request->supplierNote;
                $sold->user_id=$user_id;
                $sold->company_id=$company_id;
                $sold->update();

                $update_note = new UpdateNote();
                $update_note->RelationTable = 'sales';
                $update_note->RelationId = $Id;
                $update_note->Description = $request->update_note;
                $update_note->user_id = $user_id;
                $update_note->company_id = $company_id;
                $update_note->save();

                SaleDetail::where('sale_id',$Id)->delete();

                if(!empty($sale_detail))
                {
                    foreach ($sale_detail as $sale_item)
                    {
                        SaleDetail::create([
                            'sale_id'=>$Id,
                            'PadNumber'=>$sale_item->PadNumber,
                            'vehicle_id'=>$sale_item->vehicle_id,
                            'product_id'=>$sale_item->product_id,
                            'Price'=>$sale_item->Price,
                            'Quantity'=>$sale_item->Quantity,
                            'rowTotal'=>$sale_item->rowTotal,
                            'VAT'=>$sale_item->VAT,
                            'rowVatAmount'=>$sale_item->rowVatAmount,
                            'rowSubTotal'=>$sale_item->rowSubTotal,
                            'Description'=>$sale_item->Description,
                            'user_id'=>$user_id,
                            'company_id'=>$company_id,
                        ]);
                    }
                }
                // return statement
            }
            elseif($sold->IsPaid==0 && $sold->IsPartialPaid==1)
            {
                $sold->customer_id=$request->customer_id;
                $sold->SaleDate=$request->SaleDate;
                $sold->Total=$request->Total;
                $sold->subTotal=$request->subTotal;
                $sold->totalVat=$request->totalVat;
                $sold->grandTotal=$request->grandTotal;
                $sold->supplierNote=$request->supplierNote;
                $sold->user_id=$user_id;
                $sold->company_id=$company_id;
                $sold->update();

                $update_note = new UpdateNote();
                $update_note->RelationTable = 'sales';
                $update_note->RelationId = $Id;
                $update_note->Description = $request->update_note;
                $update_note->user_id = $user_id;
                $update_note->company_id = $company_id;
                $update_note->save();

                SaleDetail::where('sale_id',$Id)->delete();

                if(!empty($sale_detail))
                {
                    foreach ($sale_detail as $sale_item)
                    {
                        SaleDetail::create([
                            'sale_id'=>$Id,
                            'PadNumber'=>$sale_item->PadNumber,
                            'vehicle_id'=>$sale_item->vehicle_id,
                            'product_id'=>$sale_item->product_id,
                            'Price'=>$sale_item->Price,
                            'Quantity'=>$sale_item->Quantity,
                            'rowTotal'=>$sale_item->rowTotal,
                            'VAT'=>$sale_item->VAT,
                            'rowVatAmount'=>$sale_item->rowVatAmount,
                            'rowSubTotal'=>$sale_item->rowSubTotal,
                            'Description'=>$sale_item->Description,
                            'user_id'=>$user_id,
                            'company_id'=>$company_id,
                        ]);
                    }
                }
                // return statement
            }
            else
            {
                //here will come cash transaction record update if scenario will come by
                /*if ($request->paidBalance == 0.00 || $request->paidBalance == 0) {
                    $isPaid = 0;
                    $partialPaid = 0;
                }
                elseif($request->paidBalance >= $request->grandTotal)
                {
                    $isPaid = 1;
                    $partialPaid = 0;
                }
                else
                {
                    $isPaid = 0;
                    $partialPaid = 1;
                }*/

                $sold->customer_id=$request->customer_id;
                $sold->SaleDate=$request->SaleDate;
                $sold->Total=$request->Total;
                $sold->subTotal=$request->subTotal;
                $sold->totalVat=$request->totalVat;
                $sold->grandTotal=$request->grandTotal;
                $sold->supplierNote=$request->supplierNote;
                $sold->user_id=$user_id;
                $sold->company_id=$company_id;
                $sold->update();

                $update_note = new UpdateNote();
                $update_note->RelationTable = 'sales';
                $update_note->RelationId = $Id;
                $update_note->Description = $request->update_note;
                $update_note->user_id = $user_id;
                $update_note->company_id = $company_id;
                $update_note->save();

                SaleDetail::where('sale_id',$Id)->delete();

                if(!empty($sale_detail))
                {
                    foreach ($sale_detail as $sale_item)
                    {
                        SaleDetail::create([
                            'sale_id'=>$Id,
                            'PadNumber'=>$sale_item->PadNumber,
                            'vehicle_id'=>$sale_item->vehicle_id,
                            'product_id'=>$sale_item->product_id,
                            'Price'=>$sale_item->Price,
                            'Quantity'=>$sale_item->Quantity,
                            'rowTotal'=>$sale_item->rowTotal,
                            'VAT'=>$sale_item->VAT,
                            'rowVatAmount'=>$sale_item->rowVatAmount,
                            'rowSubTotal'=>$sale_item->rowSubTotal,
                            'Description'=>$sale_item->Description,
                            'user_id'=>$user_id,
                            'company_id'=>$company_id,
                        ]);
                    }
                }
                //return statement
            }
        });
        // end new code ///////////////////////////////////////////////////////////////////

        $Response = SalesResource::collection(Sale::where('id',$Id)->with('user','customer','sale_details')->get());
        $data = json_decode(json_encode($Response), true);
        return $data[0];
    }

    public function getById($Id)
    {
        $Response = SalesResource::collection(Sale::where('id',$Id)->with('user','customer','sale_details','update_notes','documents')->get());
        $data = json_decode(json_encode($Response), true);
        return $data[0];
    }

    public function SaleSearchByPad(Request $request)
    {
        $ids=SaleDetail::where('PadNumber','LIKE',"%{$request->PadNumber}%")->get();
        $ids = json_decode(json_encode($ids), true);
        $ids = array_column($ids,'sale_id');
        $Response = SalesResource::collection(Sale::whereIn('id', $ids)->with('user','customer','sale_details','update_notes','documents')->get());
        $data = json_decode(json_encode($Response), true);
        return $data;
    }

    public function BaseList()
    {
        $userId = Auth::id();
        return array('pad_number'=>$this->PadNumber(),'products'=>Product::select('id','Name')->with(['api_units'=>function($q){$q->select('id','Name','product_id');}])->orderBy('id','desc')->get(),'customer'=>Customer::select('id','Name')->with(['customer_prices'=>function($q){$q->select('id','customer_id','Rate','VAT','customerLimit');},'vehicles'=>function($q){$q->select('id','registrationNumber','customer_id');}])->where('company_id',Str::getCompany($userId))->orderBy('id','desc')->get());
    }

    public function PadNumber()
    {
        $user_id = Auth::id();
        $company_id=Str::getCompany($user_id);
        $data=array();
        $max_sales_id = SaleDetail::where('company_id',$company_id)->max('id');
        $max_sales_id = SaleDetail::where('id',$max_sales_id)->first();
        if($max_sales_id)
        {
            $lastPad = $max_sales_id->PadNumber;
            $lastDate = $max_sales_id->createdDate;
            if(!is_numeric($lastPad))
            {
                $data['pad_no']=1;
                $data['last_date']=date('Y-m-d');
            }
            else
            {
                $data['pad_no']=$lastPad + 1;
                $data['last_date']=$lastDate;
            }
        }
        else
        {
            $data['pad_no']=1;
            $data['last_date']=date('Y-m-d');
        }
        return $data;
    }

    public function delete(Request $request, $Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = Sale::find($Id);
        $update->user_id=$userId;
        $update->save();
        $sales = Sale::withoutTrashed()->find($Id);
        if($sales->trashed())
        {
            return new SalesResource(Sale::onlyTrashed()->find($Id));
        }
        else
        {
            DB::table('sale_details')->where([['sale_id', $Id]])->update(['deleted_at' =>date('Y-m-d h:i:s')]);
            //$sales->delete();
            return new SalesResource(Sale::onlyTrashed()->find($Id));
        }
    }

    public function SalesDocumentsUpload(Request $request)
    {
        try
        {
            $userId = Auth::id();
            if ($request->hasfile('document'))
            {
                foreach($request->file('document') as $document)
                {
                    $extension = $document->getClientOriginalExtension();
                    $filename=uniqid('sales_doc_'.$request->id.'_').'.'.$extension;
                    $document->storeAs('document/',$filename,'public');

                    $file_upload = new FileUpload();
                    $file_upload->Title = $filename;
                    $file_upload->RelationTable = 'sales';
                    $file_upload->RelationId = $request->id;
                    $file_upload->user_id = $userId;
                    $file_upload->save();
                }
            }
            else
            {
                return $this->userResponse->Failed("user Image","file not found");
            }
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function print($Id)
    {
        $data=$this->getById($Id);
        //echo "<pre>";print_r($data);die;
        if(!empty($data['sale_details']))
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
            $pdf::MultiCell(44, 5, $data['SaleNumber'], 0, 'R', 0, 2, '', '', true, 0);

            $pdf::SetXY(25,16);
            $pdf::MultiCell(147, 5, $company_mobile, 0, 'C', 0, 2, '', '', true, 0);

            $pdf::SetXY(25,20);
            $pdf::MultiCell(107, 5, $company_email, 0, 'C', 0, 2, '', '', true, 0);
            $pdf::MultiCell(71, 5, 'Date : '.date('d-m-Y', strtotime($data['SaleDate'])), 0, 'R', 0, 2, '', '', true, 0);

            $pdf::SetXY(25,24);
            $pdf::MultiCell(106, 5, 'TRN : 100330389600003', 0, 'C', 0, 2, '', '', true, 0);
            $pdf::MultiCell(72, 5, 'Due Date : '.date('d-m-Y', strtotime($data['DueDate'])), 0, 'R', 0, 2, '', '', true, 0);

            $pdf::SetXY(28,28);
            $pdf::Image('https://watanpharma.com/images/logo-1.png', 15, 5, 40, 18, 'PNG', '', '', true, 300, '', false, false, 0, false, false, false);
            $pdf::SetXY(15,37);
            $pdf::Ln(6);

            $pdf::SetXY(25,35);
            $pdf::writeHTML("<hr>", true, false, false, false, '');

            $row=$data['sale_details'];
            $pdf::SetFont('times', '', 15);
            $html='<u><b>SALES INVOICE</b></u>';
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
            $fileLocation = storage_path().'/app/public/sales_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/sales_files/'.$time.'.pdf';
            //$url=storage_path().'/purchase_order_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return $this->userResponse->Failed($purchase = (object)[],'Not Found.');
        }
    }

    public function ActivateDeactivate($Id)
    {
        $sales = Sale::find($Id);
        if($sales->isActive==1)
        {
            $sales->isActive=0;
        }
        else
        {
            $sales->isActive=1;
        }
        //$sales->update();
        return new SalesResource(Sale::find($Id));
    }

    public function customerSaleDetails($Id)
    {
        $sales = Sale::with('customer.vehicles','sale_details')
            ->where([
                'customer_id'=>$Id,
                'IsPaid'=> false,
            ])->get();
        return $sales;
    }
}
