<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\IPurchaseRepositoryInterface;
use App\Http\Requests\PurchaseRequest;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Purchase\PurchaseResource;
use App\Models\AccountTransaction;
use App\Models\CashTransaction;
use App\Models\FileUpload;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use App\Models\UpdateNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PDF;

class PurchaseRepository implements IPurchaseRepositoryInterface
{
    public function all()
    {
        return PurchaseResource::collection(Purchase::with('purchase_details')->get()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return PurchaseResource::Collection(Purchase::with('purchase_details','update_notes','documents')->get()->sortDesc()->forPage($page_no,$page_size));
    }

    public function ActivateDeactivate($Id)
    {
        $purchase = Purchase::find($Id);
        if($purchase->isActive==1)
        {
            $purchase->isActive=0;
        }
        else
        {
            $purchase->isActive=1;
        }
        $purchase->update();
        return new PurchaseResource(Purchase::find($Id));
    }

    public function insert(Request $request)
    {
        /* start of new code */
        $purchase_id=0;
        DB::transaction(function () use($request,&$purchase_id)
        {
            $user_id = Auth::id();
            $company_id=Str::getCompany($user_id);

            $purchase_detail=json_decode($_POST['pd']);
            $this_pad_no = $purchase_detail[0]->PadNumber;

            if ($request->paidBalance == 0.00 || $request->paidBalance == 0)
            {
                $isPaid_current = 0;
                $partialPaid_current = 0;
            }
            elseif ($request->paidBalance >= $request->grandTotal)
            {
                $isPaid_current = 1;
                $partialPaid_current = 0;
            }
            else
            {
                $isPaid_current = 0;
                $partialPaid_current = 1;
            }

            $invoice = new Purchase();
            $lastInvoiceID = $invoice->orderByDesc('id')->pluck('id')->first();
            $newInvoiceID = 'PUR-00'.($lastInvoiceID + 1);

            $purchase = new Purchase();
            $purchase->PurchaseNumber = $newInvoiceID;
            $purchase->referenceNumber = $request->referenceNumber;
            $purchase->PurchaseDate = $request->PurchaseDate;
            $purchase->DueDate = $request->DueDate;
            $purchase->Total = $request->Total;
            $purchase->subTotal = $request->subTotal;
            $purchase->totalVat = $request->totalVat;
            $purchase->grandTotal = $request->grandTotal;
            $purchase->paidBalance = $request->paidBalance;
            $purchase->remainingBalance = $request->remainingBalance;
            $purchase->supplier_id = $request->supplier_id;
            $purchase->supplierNote = $request->supplierNote;
            $purchase->IsPaid = $isPaid_current;
            $purchase->IsPartialPaid = $partialPaid_current;
            $purchase->IsNeedStampOrSignature = false;
            $purchase->user_id = $user_id;
            $purchase->company_id = $company_id;
            $purchase->createdDate=date('Y-m-d h:i:s');
            $purchase->isActive=1;
            $purchase->save();
            $purchase = $purchase->id;
            $purchase_id=$purchase;

            $purchase_detail=json_decode($_POST['pd']);
            foreach ($purchase_detail as $purchase_item)
            {
                PurchaseDetail::create([
                    'purchase_id'=>$purchase,
                    'PadNumber'=>$purchase_item->PadNumber,
                    'product_id'=>$purchase_item->product_id,
                    'unit_id'=>$purchase_item->unit_id,
                    'Price'=>$purchase_item->Price,
                    'Quantity'=>$purchase_item->Quantity,
                    'rowTotal'=>$purchase_item->rowTotal,
                    'VAT'=>$purchase_item->VAT,
                    'rowVatAmount'=>$purchase_item->rowVatAmount,
                    'rowSubTotal'=>$purchase_item->rowSubTotal,
                    'Description'=>$purchase_item->Description,
                    'user_id'=>$user_id,
                    'company_id'=>$company_id,
                    'createdDate'=>date('Y-m-d h:i:s'),
                ]);
            }

            if ($request->paidBalance != 0.00 || $request->paidBalance != 0)
            {
                $cashTransaction = CashTransaction::where(['company_id' => $company_id])->get();
                $difference = $cashTransaction->last()->Differentiate;
                $cash_transaction = new CashTransaction();
                $cash_transaction->Reference = $purchase;
                $cash_transaction->createdDate = $request->PurchaseDate;
                $cash_transaction->Type = 'purchases';
                $cash_transaction->Details = 'CashPurchase|' . $purchase;
                $cash_transaction->Credit = $request->paidBalance;
                $cash_transaction->Debit = 0.00;
                $cash_transaction->Differentiate = $difference - $request->paidBalance;
                $cash_transaction->user_id = $user_id;
                $cash_transaction->company_id = $company_id;
                $cash_transaction->save();
            }

            ////////////////// start account section gautam ////////////////
            if ($purchase)
            {
                $accountTransaction = AccountTransaction::where(['supplier_id' => $request->supplier_id,])->get();
                // totally credit
                if ($request->paidBalance == 0 || $request->paidBalance == 0.00)
                {
                    $totalCredit = $request->grandTotal;
                    $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;

                    $AccData =
                        [
                            'supplier_id' => $request->supplier_id,
                            'Credit' => $totalCredit,
                            'Debit' => 0.00,
                            'Differentiate' => $difference,
                            'createdDate' => $request->PurchaseDate,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description' => 'Purchase|' . $purchase,
                            'referenceNumber' => $this_pad_no,
                        ];
                    $AccountTransactions = AccountTransaction::Create($AccData);
                } // partial payment some cash some credit
                elseif ($request->paidBalance > 0 and $request->paidBalance < $request->grandTotal)
                {
                    $differenceValue = $accountTransaction->last()->Differentiate - $request->paidBalance;
                    $totalCredit = $accountTransaction->last()->Differentiate + $request->grandTotal;
                    $difference = $differenceValue + $request->grandTotal;

                    //make credit entry for the purchase
                    $AccData =
                        [
                            'supplier_id' => $request->supplier_id,
                            'Credit' => $request->grandTotal,
                            'Debit' => 0.00,
                            'Differentiate' => $totalCredit,
                            'createdDate' => $request->PurchaseDate,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description' => 'Purchase|' . $purchase,
                            'referenceNumber' => $this_pad_no,
                        ];
                    $AccountTransactions = AccountTransaction::Create($AccData);

                    //make debit entry for the whatever cash is paid
                    $difference = $totalCredit - $request->paidBalance;
                    $AccData =
                        [
                            'supplier_id' => $request->supplier_id,
                            'Credit' => 0.00,
                            'Debit' => $request->paidBalance,
                            'Differentiate' => $difference,
                            'createdDate' => $request->PurchaseDate,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description' => 'PartialCashPurchase|' . $purchase,
                            'referenceNumber' => $this_pad_no,
                        ];
                    $AccountTransactions = AccountTransaction::Create($AccData);
                } // fully paid with cash
                else
                {
                    $totalCredit = $request->grandTotal;
                    $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;

                    //make credit entry for the purchase
                    $AccountTransactions = AccountTransaction::Create([
                        'supplier_id' => $request->supplier_id,
                        'Credit' => $totalCredit,
                        'Debit' => 0.00,
                        'Differentiate' => $difference,
                        'createdDate' => $request->PurchaseDate,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description' => 'Purchase|' . $purchase,
                        'referenceNumber' => $this_pad_no,
                    ]);

                    //make debit entry for the whatever cash is paid
                    $difference = $difference - $request->paidBalance;
                    $AccountTransactions = AccountTransaction::Create([
                        'supplier_id' => $request->supplier_id,
                        'Credit' => 0.00,
                        'Debit' => $request->paidBalance,
                        'Differentiate' => $difference,
                        'createdDate' => $request->PurchaseDate,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description' => 'FullCashPurchase|' . $purchase,
                        'referenceNumber' => $this_pad_no,
                    ]);
                }
                return Response()->json($AccountTransactions);
                // return Response()->json("");
            }
            ////////////////// end account section gautam ////////////////
        });
        /* end of new code */
        $Response = PurchaseResource::collection(Purchase::where('id',$purchase_id)->with('user','supplier','purchase_details')->get());
        $data = json_decode(json_encode($Response), true);
        return $data[0];
    }

    public function update(Request $request, $Id)
    {
        /* start of new code */
        DB::transaction(function () use($request,$Id)
        {
            $purchased = Purchase::find($Id);
            $user_id = Auth::id();
            $company_id=Str::getCompany($user_id);

            ////////////////// account section gautam ////////////////
            $accountTransaction = AccountTransaction::where(['supplier_id' => $purchased->supplier_id,])->get();
            if (!is_null($accountTransaction))
            {
                // payment is done (in any way - advance or payment)
                if ($purchased->IsPaid == 1 && $purchased->IsPartialPaid == 0)
                {
                    // if more cash incoming then need to add in supplier account

                    //check if only supplier is changed and not quantity or price = grand total is same as previous
                    if ($request->supplier_id != $purchased->supplier_id and $purchased->grandTotal == $request->grandTotal)
                    {
                        //supplier is changed need to reverse all previously made account entries for the previous supplier

                        // start reverse entry for wrong supplier
                        $last_closing = $accountTransaction->last()->Differentiate;
                        $description_string = 'Purchase|' . $Id;
                        $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                        $previously_credited = $previous_entry->Credit;
                        $AccData =
                            [
                                'supplier_id' => $purchased->supplier_id,
                                'Debit' => $previously_credited,
                                'Credit' => 0.00,
                                'Differentiate' => $last_closing - $previously_credited,
                                'createdDate' => $request->PurchaseDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description' => 'Purchase|' . $Id,
                                'updateDescription' => 'hide',
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // also hide previous entry start
                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                        // also hide previous entry end
                        // reverse entry done for wrong supplier

                        /*new entry*/
                        // start new entry for right supplier and credit or debit account based on closing balance
                        $accountTransaction = AccountTransaction::where(['supplier_id' => $request->supplier_id,])->get();
                        $totalCredit = $request->grandTotal;
                        $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;
                        $AccData =
                            [
                                'supplier_id' => $request->supplier_id,
                                'Credit' => $totalCredit,
                                'Debit' => 0.00,
                                'Differentiate' => $difference,
                                'createdDate' => $request->PurchaseDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description' => 'Purchase|' . $Id,
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        if ($difference < 0)
                        {
                            // still there is advance amount so make it fully paid
                            $this_purchase = Purchase::find($purchased->id);
                            $this_purchase->update([
                                "paidBalance" => $request->grandTotal,
                                "remainingBalance" => 0.00,
                                "IsPaid" => 1,
                                "IsPartialPaid" => 0,
                                "IsNeedStampOrSignature" => false,
                                "Description" => 'AutoPaid',
                            ]);
                        }
                        elseif ($difference > 0)
                        {
                            if ($difference == ($request->grandTotal))
                            {
                                // now we are payable so differance amount will be paid amount and make it partial paid
                                $this_purchase = Purchase::find($purchased->id);
                                $this_purchase->update([
                                    "paidBalance" => 0,
                                    "remainingBalance" => $difference,
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
                                    "paidBalance" => $request->grandTotal - $difference,
                                    "remainingBalance" => $difference,
                                    "IsPaid" => 0,
                                    "IsPartialPaid" => 1,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid',
                                ]);
                            }
                        }
                        /*new entry*/
                    } // check if only grand total is changed and not the supplier
                    elseif ($request->supplier_id == $purchased->supplier_id and $purchased->grandTotal != $request->grandTotal)
                    {
                        // 1 : reverse older entry
                        // start reverse entry
                        $last_closing = $accountTransaction->last()->Differentiate;
                        $description_string = 'Purchase|' . $Id;
                        $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                        $previously_credited = $previous_entry->Credit;
                        $AccData =
                            [
                                'supplier_id' => $purchased->supplier_id,
                                'Debit' => $previously_credited,
                                'Credit' => 0.00,
                                'Differentiate' => $last_closing - $previously_credited,
                                'createdDate' => $request->PurchaseDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description' => 'Purchase|' . $Id,
                                'updateDescription' => 'hide',
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // also hide previous entry start
                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                        // also hide previous entry end
                        // reverse entry done

                        /* new entry start */
                        // make new entry then check account balance
                        $accountTransaction = AccountTransaction::where(['supplier_id' => $request->supplier_id,])->get();

                        $totalCredit = $request->grandTotal;
                        $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;
                        $AccData =
                            [
                                'supplier_id' => $request->supplier_id,
                                'Credit' => $totalCredit,
                                'Debit' => 0.00,
                                'Differentiate' => $difference,
                                'createdDate' => $request->PurchaseDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description' => 'Purchase|' . $Id,
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        $accountTransaction_ref = $AccountTransactions->id;
                        /* new entry end */

                        // if difference is positive meaning advance is over and we are payable
                        // so update purchase entry with difference amount as paid amount
                        if ($difference < 0)
                        {
                            // still there is advance amount so make it fully paid
                            $this_purchase = Purchase::find($purchased->id);
                            $this_purchase->update([
                                "paidBalance" => $request->grandTotal,
                                "remainingBalance" => 0.00,
                                "IsPaid" => 1,
                                "IsPartialPaid" => 0,
                                "IsNeedStampOrSignature" => false,
                                "Description" => 'AutoPaid',
                            ]);
                        }
                        elseif ($difference > 0)
                        {
                            if ($difference == ($request->grandTotal))
                            {
                                // now we are payable so differance amount will be paid amount and make it partial paid
                                $this_purchase = Purchase::find($purchased->id);
                                $this_purchase->update([
                                    "paidBalance" => $request->grandTotal,
                                    "remainingBalance" => $difference,
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
                                    "paidBalance" => $request->grandTotal - $difference,
                                    "remainingBalance" => $difference,
                                    "IsPaid" => 0,
                                    "IsPartialPaid" => 1,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid',
                                ]);
                            }
                        }
                        // fully paid case will come here
                    } // check both supplier and grandTotal is changed meaning case 3
                    elseif ($request->supplier_id != $purchased->supplier_id and $purchased->grandTotal != $request->grandTotal)
                    {
                        // start reverse entry for wrong supplier with wrong entries
                        $last_closing = $accountTransaction->last()->Differentiate;
                        $description_string = 'Purchase|' . $Id;
                        $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                        $previously_credited = $previous_entry->Credit;
                        $AccData =
                            [
                                'supplier_id' => $purchased->supplier_id,
                                'Debit' => $previously_credited,
                                'Credit' => 0.00,
                                'Differentiate' => $last_closing - $previously_credited,
                                'createdDate' => $request->PurchaseDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description' => 'Purchase|' . $Id,
                                'updateDescription' => 'hide',
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // also hide previous entry start
                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                        // also hide previous entry end
                        // reverse entry done for wrong supplier

                        /*new entry with right grand total */
                        // start new entry for right supplier and credit or debit account based on closing balance
                        $accountTransaction = AccountTransaction::where(['supplier_id' => $request->supplier_id,])->get();
                        $totalCredit = $request->grandTotal;
                        $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;
                        $AccData =
                            [
                                'supplier_id' => $request->supplier_id,
                                'Credit' => $totalCredit,
                                'Debit' => 0.00,
                                'Differentiate' => $difference,
                                'createdDate' => $request->PurchaseDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description' => 'Purchase|' . $Id,
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        if ($difference < 0) {
                            // still there is advance amount so make it fully paid
                            $this_purchase = Purchase::find($purchased->id);
                            $this_purchase->update([
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
                                $this_purchase = Purchase::find($purchased->id);
                                $this_purchase->update([
                                    "paidBalance" => $request->grandTotal,
                                    "remainingBalance" => $difference,
                                    "IsPaid" => 1,
                                    "IsPartialPaid" => 0,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'AutoPaid',
                                ]);
                            } else {
                                // now we are payable so differance amount will be paid amount and make it partial paid
                                $this_purchase = Purchase::find($purchased->id);
                                $this_purchase->update([
                                    "paidBalance" => $request->grandTotal - $difference,
                                    "remainingBalance" => $difference,
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
                } // payment is done (in any way - advance or payment)
                elseif ($purchased->IsPaid == 0 && $purchased->IsPartialPaid == 1) {
                    // if more cash incoming then need to add in supplier account

                    //check if only supplier is changed and not quantity or price = grand total is same as previous
                    if ($request->supplier_id != $purchased->supplier_id and $purchased->grandTotal == $request->grandTotal) {
                        //supplier is changed need to reverse all previously made account entries for the previous supplier

                        //case : 1 full credit entry + payment is not done yet like isPaid=0 and IsPartialPaid=0
                        if ($purchased->IsPaid == 0 && $purchased->IsPartialPaid == 0) {
                            // start reverse entry
                            $last_closing = $accountTransaction->last()->Differentiate;
                            $description_string = 'Purchase|' . $Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_credited = $previous_entry->Credit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing - $previously_credited,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'Purchase|' . $Id,
                                    'updateDescription' => 'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done
                        } //case : 2 partial cash is paid and some amount is credit + payment is not fully done yet like isPaid=0 and IsPartialPaid=1
                        elseif ($purchased->paidBalance > 0 and $purchased->paidBalance < $purchased->grandTotal and $purchased->IsPartialPaid == 1) {
                            // entry 1 : debit entry for purchase
                            // start reverse entry
                            $last_closing = $accountTransaction->last()->Differentiate;
                            $description_string = 'Purchase|' . $Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_credited = $previous_entry->Credit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing - $previously_credited,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'Purchase|' . $Id,
                                    'updateDescription' => 'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done
                            // entry 2 : credit whatever cash is debited
                            // start reverse entry
                            $accountTransaction = AccountTransaction::where(['supplier_id' => $purchased->supplier_id,])->get();
                            $last_closing = $accountTransaction->last()->Differentiate;
                            $description_string = 'PartialCashPurchase|' . $Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_debited = $previous_entry->Debit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => 0.00,
                                    'Credit' => $previously_debited,
                                    'Differentiate' => $last_closing + $previously_debited,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'PartialCashPurchase|' . $Id,
                                    'updateDescription' => 'hide',
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
                        } //case : 3 fully cash paid + isPaid=1 and IsPartialPaid=1
                        else {
                            // entry 1 : debit entry for purchase
                            // start reverse entry
                            $last_closing = $accountTransaction->last()->Differentiate;
                            $description_string = 'Purchase|' . $Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_credited = $previous_entry->Credit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing - $previously_credited,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'Purchase|' . $Id,
                                    'updateDescription' => 'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done
                            // entry 2 : credit whatever cash is debited
                            // start reverse entry
                            $accountTransaction = AccountTransaction::where(['supplier_id' => $purchased->supplier_id,])->get();
                            $last_closing = $accountTransaction->last()->Differentiate;
                            $description_string = 'FullCashPurchase|' . $Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_debited = $previous_entry->Debit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => 0.00,
                                    'Credit' => $previously_debited,
                                    'Differentiate' => $last_closing + $previously_debited,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'FullCashPurchase|' . $Id,
                                    'updateDescription' => 'hide',
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
                        $accountTransaction = AccountTransaction::where(['supplier_id' => $request->supplier_id,])->get();
                        // totally credit
                        if ($request->paidBalance == 0 || $request->paidBalance == 0.00) {
                            $totalCredit = $request->grandTotal;
                            $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;

                            $AccData =
                                [
                                    'supplier_id' => $request->supplier_id,
                                    'Credit' => $totalCredit,
                                    'Debit' => 0.00,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'Purchase|' . $Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                        } // partial payment some cash some credit
                        elseif ($request->paidBalance > 0 and $request->paidBalance < $request->grandTotal) {
                            $differenceValue = $accountTransaction->last()->Differentiate - $request->paidBalance;
                            $totalCredit = $accountTransaction->last()->Differentiate + $request->grandTotal;
                            $difference = $differenceValue + $request->grandTotal;

                            //make credit entry for the purchase
                            $AccData =
                                [
                                    'supplier_id' => $request->supplier_id,
                                    'Credit' => $request->grandTotal,
                                    'Debit' => 0.00,
                                    'Differentiate' => $totalCredit,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'Purchase|' . $Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);

                            //make debit entry for the whatever cash is paid
                            $difference = $totalCredit - $request->paidBalance;
                            $AccData =
                                [
                                    'supplier_id' => $request->supplier_id,
                                    'Credit' => 0.00,
                                    'Debit' => $request->paidBalance,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'PartialCashPurchase|' . $Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                        } // fully paid with cash
                        else {
                            $totalCredit = $request->grandTotal;
                            $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;

                            //make credit entry for the purchase
                            $AccountTransactions = AccountTransaction::Create([
                                'supplier_id' => $request->supplier_id,
                                'Credit' => $totalCredit,
                                'Debit' => 0.00,
                                'Differentiate' => $difference,
                                'createdDate' => $request->PurchaseDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description' => 'Purchase|' . $Id,
                            ]);

                            //make debit entry for the whatever cash is paid
                            $difference = $difference - $request->paidBalance;
                            $AccountTransactions = AccountTransaction::Create([
                                'supplier_id' => $request->supplier_id,
                                'Credit' => 0.00,
                                'Debit' => $request->paidBalance,
                                'Differentiate' => $difference,
                                'createdDate' => $request->PurchaseDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description' => 'FullCashPurchase|' . $Id,
                            ]);
                        }
                        /*new entry*/
                    } // check if only grand total is changed and not the supplier
                    elseif ($request->supplier_id == $purchased->supplier_id and $purchased->grandTotal != $request->grandTotal) {
                        // 1 : reverse older entry
                        // start reverse entry
                        $last_closing = $accountTransaction->last()->Differentiate;
                        $description_string = 'Purchase|' . $Id;
                        $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                        $previously_credited = $previous_entry->Credit;
                        $AccData =
                            [
                                'supplier_id' => $purchased->supplier_id,
                                'Debit' => $previously_credited,
                                'Credit' => 0.00,
                                'Differentiate' => $last_closing - $previously_credited,
                                'createdDate' => $request->PurchaseDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description' => 'Purchase|' . $Id,
                                'updateDescription' => 'hide',
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // also hide previous entry start
                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                        // also hide previous entry end
                        // reverse entry done

                        /* new entry start */
                        // make new entry then check account balance
                        $accountTransaction = AccountTransaction::where(['supplier_id' => $request->supplier_id,])->get();

                        $totalCredit = $request->grandTotal;
                        $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;
                        $AccData =
                            [
                                'supplier_id' => $request->supplier_id,
                                'Credit' => $totalCredit,
                                'Debit' => 0.00,
                                'Differentiate' => $difference,
                                'createdDate' => $request->PurchaseDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description' => 'Purchase|' . $Id,
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        $accountTransaction_ref = $AccountTransactions->id;
                        /* new entry end */

                        // if difference is positive meaning advance is over and we are payable
                        // so update purchase entry with difference amount as paid amount
                        if ($difference < 0) {
                            // meaning after paying new amount there is still advance amount
                            $this_purchase = Purchase::find($purchased->id);
                            $this_purchase->update([
                                "paidBalance" => $request->grandTotal,
                                "remainingBalance" => 0.00,
                                "IsPaid" => 1,
                                "IsPartialPaid" => 0,
                                "IsNeedStampOrSignature" => false,
                                "Description" => 'AutoPaid',
                            ]);
                        } elseif ($difference > 0) {
                            // now we are payable so differance amount will be paid amount and make it partial paid
                            $this_purchase = Purchase::find($purchased->id);
                            $this_purchase->update([
                                "paidBalance" => $request->grandTotal - $difference,
                                "remainingBalance" => $difference,
                                "IsPaid" => 0,
                                "IsPartialPaid" => 1,
                                "IsNeedStampOrSignature" => false,
                                "Description" => 'AutoPaid',
                            ]);
                        }
                        // fully paid case will come here
                    } // check both supplier and grandTotal is changed meaning case 3
                    elseif ($request->supplier_id != $purchased->supplier_id and $purchased->grandTotal != $request->grandTotal) {
                        //case : 1 full credit entry + payment is not done yet like isPaid=0 and IsPartialPaid=0
                        if ($purchased->IsPaid == 0 && $purchased->IsPartialPaid == 0) {
                            // start reverse entry
                            $last_closing = $accountTransaction->last()->Differentiate;
                            $description_string = 'Purchase|' . $Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_credited = $previous_entry->Credit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing - $previously_credited,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'Purchase|' . $Id,
                                    'updateDescription' => 'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done
                        } //case : 2 partial cash is paid and some amount is credit + payment is not fully done yet like isPaid=0 and IsPartialPaid=1
                        elseif ($purchased->paidBalance > 0 and $purchased->paidBalance < $purchased->grandTotal and $purchased->IsPartialPaid == 1) {
                            // entry 1 : debit entry for purchase
                            // start reverse entry
                            $last_closing = $accountTransaction->last()->Differentiate;
                            $description_string = 'Purchase|' . $Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_credited = $previous_entry->Credit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing - $previously_credited,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'Purchase|' . $Id,
                                    'updateDescription' => 'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done
                            // entry 2 : credit whatever cash is debited
                            // start reverse entry
                            $accountTransaction = AccountTransaction::where(['supplier_id' => $purchased->supplier_id,])->get();
                            $last_closing = $accountTransaction->last()->Differentiate;
                            $description_string = 'PartialCashPurchase|' . $Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_debited = $previous_entry->Debit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => 0.00,
                                    'Credit' => $previously_debited,
                                    'Differentiate' => $last_closing + $previously_debited,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'PartialCashPurchase|' . $Id,
                                    'updateDescription' => 'hide',
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
                        } //case : 3 fully cash paid + isPaid=1 and IsPartialPaid=1
                        else {
                            // entry 1 : debit entry for purchase
                            // start reverse entry
                            $last_closing = $accountTransaction->last()->Differentiate;
                            $description_string = 'Purchase|' . $Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_credited = $previous_entry->Credit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing - $previously_credited,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'Purchase|' . $Id,
                                    'updateDescription' => 'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done

                            // entry 2 : credit whatever cash is debited
                            // start reverse entry
                            $accountTransaction = AccountTransaction::where(['supplier_id' => $purchased->supplier_id,])->get();
                            $last_closing = $accountTransaction->last()->Differentiate;
                            $description_string = 'FullCashPurchase|' . $Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_debited = $previous_entry->Debit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => 0.00,
                                    'Credit' => $previously_debited,
                                    'Differentiate' => $last_closing + $previously_debited,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'FullCashPurchase|' . $Id,
                                    'updateDescription' => 'hide',
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
                        $accountTransaction = AccountTransaction::where(['supplier_id' => $request->supplier_id,])->get();
                        // totally credit
                        if ($request->paidBalance == 0 || $request->paidBalance == 0.00) {
                            $totalCredit = $request->grandTotal;
                            $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;

                            $AccData =
                                [
                                    'supplier_id' => $request->supplier_id,
                                    'Credit' => $totalCredit,
                                    'Debit' => 0.00,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'Purchase|' . $Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                        } // partial payment some cash some credit
                        elseif ($request->paidBalance > 0 and $request->paidBalance < $request->grandTotal) {
                            $differenceValue = $accountTransaction->last()->Differentiate - $request->paidBalance;
                            $totalCredit = $accountTransaction->last()->Differentiate + $request->grandTotal;
                            $difference = $differenceValue + $request->grandTotal;

                            //make credit entry for the purchase
                            $AccData =
                                [
                                    'supplier_id' => $request->supplier_id,
                                    'Credit' => $request->grandTotal,
                                    'Debit' => 0.00,
                                    'Differentiate' => $totalCredit,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'Purchase|' . $Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);

                            //make debit entry for the whatever cash is paid
                            $difference = $totalCredit - $request->paidBalance;
                            $AccData =
                                [
                                    'supplier_id' => $request->supplier_id,
                                    'Credit' => 0.00,
                                    'Debit' => $request->paidBalance,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'PartialCashPurchase|' . $Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                        } // fully paid with cash
                        else {
                            $totalCredit = $request->grandTotal;
                            $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;

                            //make credit entry for the purchase
                            $AccountTransactions = AccountTransaction::Create([
                                'supplier_id' => $request->supplier_id,
                                'Credit' => $totalCredit,
                                'Debit' => 0.00,
                                'Differentiate' => $difference,
                                'createdDate' => $request->PurchaseDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description' => 'Purchase|' . $Id,
                            ]);

                            //make debit entry for the whatever cash is paid
                            $difference = $difference - $request->paidBalance;
                            $AccountTransactions = AccountTransaction::Create([
                                'supplier_id' => $request->supplier_id,
                                'Credit' => 0.00,
                                'Debit' => $request->paidBalance,
                                'Differentiate' => $difference,
                                'createdDate' => $request->PurchaseDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description' => 'FullCashPurchase|' . $Id,
                            ]);
                        }
                        /*new entry*/
                    }
                    //return Response()->json($accountTransaction);
                } //payment not done
                elseif ($purchased->IsPaid == 0 && $purchased->IsPartialPaid == 0)
                {
                    // if paid balance is not same as earlier need to update cash account as well
                    if ($purchased->paidBalance != $request->paidBalance)
                    {
                        //check if previously cash transaction done with this purchase id
                        $description_string = 'CashPurchase|' . $Id;
                        $previous_cash_entry = CashTransaction::get()->where('company_id', '=', $company_id)->where('Details', 'like', $description_string)->last();
                        if ($previous_cash_entry)
                        {
                            // start reverse entry
                            $previously_credited = $previous_cash_entry->Credit;
                            $cashTransaction = CashTransaction::where(['company_id' => $company_id])->get();
                            $difference = $cashTransaction->last()->Differentiate;
                            $cash_transaction = new CashTransaction();
                            $cash_transaction->Reference = $Id;
                            $cash_transaction->createdDate = $request->PurchaseDate;
                            $cash_transaction->Type = 'purchases';
                            $cash_transaction->Details = 'CashPurchase|' . $Id . 'hide';
                            $cash_transaction->Credit = 0.00;
                            $cash_transaction->Debit = $previously_credited;
                            $cash_transaction->Differentiate = $difference + $previously_credited;
                            $cash_transaction->user_id = $user_id;
                            $cash_transaction->company_id = $company_id;
                            $cash_transaction->save();
                            // also hide previous entry start
                            CashTransaction::where('id', $previous_cash_entry->id)->update(array('Details' => 'CashPurchase|' . $Id . 'hide'));
                            // also hide previous entry end
                            // reverse entry done

                            // start new entry
                            $cashTransaction = CashTransaction::where(['company_id' => $company_id])->get();
                            $difference = $cashTransaction->last()->Differentiate;
                            $cash_transaction = new CashTransaction();
                            $cash_transaction->Reference = $Id;
                            $cash_transaction->createdDate = $request->PurchaseDate;
                            $cash_transaction->Type = 'purchases';
                            $cash_transaction->Details = 'CashPurchase|' . $Id;
                            $cash_transaction->Credit = $request->paidBalance;
                            $cash_transaction->Debit = 0.00;
                            $cash_transaction->Differentiate = $difference - $request->paidBalance;
                            $cash_transaction->user_id = $user_id;
                            $cash_transaction->company_id = $company_id;
                            $cash_transaction->save();
                            // end new entry

                            // now here we check if only and only cash paid is updating and none of the below case will execute then we need..
                            // to check if there any existing entry with PartialCashPurchase|$id and not hidden we need to reverse that entry
                            if ($request->supplier_id == $purchased->supplier_id and $purchased->grandTotal == $request->grandTotal)
                            {
                                $description_string = 'PartialCashPurchase|' . $Id;
                                $previous_entry = AccountTransaction::get()->where('company_id', '=', $company_id)->where('Description', 'like', $description_string)->last();
                                if ($previous_entry)
                                {
                                    // start revers entry
                                    $accountTransaction = AccountTransaction::where(['supplier_id' => $purchased->supplier_id,])->get();
                                    $last_closing = $accountTransaction->last()->Differentiate;
                                    $previously_debited = $previous_entry->Debit;
                                    $AccData =
                                        [
                                            'supplier_id' => $purchased->supplier_id,
                                            'Debit' => 0.00,
                                            'Credit' => $previously_debited,
                                            'Differentiate' => $last_closing + $previously_debited,
                                            'createdDate' => $request->PurchaseDate,
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description' => 'PartialCashPurchase|' . $Id,
                                            'updateDescription' => 'hide',
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);
                                    // also hide previous entry start
                                    AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                    // also hide previous entry end
                                    // reverse entry done

                                    // start new entry
                                    $accountTransaction = AccountTransaction::where(['supplier_id' => $purchased->supplier_id,])->get();
                                    $last_closing = $accountTransaction->last()->Differentiate;
                                    $AccData =
                                        [
                                            'supplier_id' => $purchased->supplier_id,
                                            'Debit' => $request->paidBalance,
                                            'Credit' => 0.00,
                                            'Differentiate' => $last_closing - $request->paidBalance,
                                            'createdDate' => $request->PurchaseDate,
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description' => 'PartialCashPurchase|' . $Id,
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);
                                    // new entry done
                                }
                            }
                        }
                        else
                        {
                            // start new entry
                            $cashTransaction = CashTransaction::where(['company_id' => $company_id])->get();
                            $difference = $cashTransaction->last()->Differentiate;
                            $cash_transaction = new CashTransaction();
                            $cash_transaction->Reference = $Id;
                            $cash_transaction->createdDate = $request->PurchaseDate;
                            $cash_transaction->Type = 'purchases';
                            $cash_transaction->Details = 'CashPurchase|' . $Id;
                            $cash_transaction->Credit = $request->paidBalance;
                            $cash_transaction->Debit = 0.00;
                            $cash_transaction->Differentiate = $difference - $request->paidBalance;
                            $cash_transaction->user_id = $user_id;
                            $cash_transaction->company_id = $company_id;
                            $cash_transaction->save();
                            // end new entry

                            // now here we check if only and only cash paid is updating and none of the below case will execute then we need..
                            // to create one more cash entry for this purchase as partial cash purchase entry in account transaction
                            if ($request->supplier_id == $purchased->supplier_id and $purchased->grandTotal == $request->grandTotal) {
                                $accountTransaction = AccountTransaction::where(['supplier_id' => $purchased->supplier_id,])->get();
                                $last_closing = $accountTransaction->last()->Differentiate;
                                $description_string = 'Purchase|' . $Id;
                                $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_debited = $previous_entry->Debit;
                                $AccData =
                                    [
                                        'supplier_id' => $purchased->supplier_id,
                                        'Debit' => $request->paidBalance,
                                        'Credit' => 0.00,
                                        'Differentiate' => $last_closing - $request->paidBalance,
                                        'createdDate' => $request->PurchaseDate,
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description' => 'PartialCashPurchase|' . $Id,
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
                    if ($request->supplier_id != $purchased->supplier_id and $purchased->grandTotal == $request->grandTotal)
                    {
                        //supplier is changed need to reverse all previously made account entries for the previous supplier

                        //case : 1 full credit entry + payment is not done yet like isPaid=0 and IsPartialPaid=0
                        if ($purchased->IsPaid == 0 && $purchased->IsPartialPaid == 0) {
                            // start reverse entry
                            $last_closing = $accountTransaction->last()->Differentiate;
                            $description_string = 'Purchase|' . $Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_credited = $previous_entry->Credit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing - $previously_credited,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'Purchase|' . $Id,
                                    'updateDescription' => 'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done
                        } //case : 2 partial cash is paid and some amount is credit + payment is not fully done yet like isPaid=0 and IsPartialPaid=1
                        elseif ($purchased->paidBalance > 0 and $purchased->paidBalance < $purchased->grandTotal and $purchased->IsPartialPaid == 1) {
                            // entry 1 : debit entry for purchase
                            // start reverse entry
                            $last_closing = $accountTransaction->last()->Differentiate;
                            $description_string = 'Purchase|' . $Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_credited = $previous_entry->Credit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing - $previously_credited,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'Purchase|' . $Id,
                                    'updateDescription' => 'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done
                            // entry 2 : credit whatever cash is debited
                            // start reverse entry
                            $accountTransaction = AccountTransaction::where(['supplier_id' => $purchased->supplier_id,])->get();
                            $last_closing = $accountTransaction->last()->Differentiate;
                            $description_string = 'PartialCashPurchase|' . $Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_debited = $previous_entry->Debit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => 0.00,
                                    'Credit' => $previously_debited,
                                    'Differentiate' => $last_closing + $previously_debited,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'PartialCashPurchase|' . $Id,
                                    'updateDescription' => 'hide',
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
                        } //case : 3 fully cash paid + isPaid=1 and IsPartialPaid=1
                        else {
                            // entry 1 : debit entry for purchase
                            // start reverse entry
                            $last_closing = $accountTransaction->last()->Differentiate;
                            $description_string = 'Purchase|' . $Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_credited = $previous_entry->Credit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing - $previously_credited,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'Purchase|' . $Id,
                                    'updateDescription' => 'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done
                            // entry 2 : credit whatever cash is debited
                            // start reverse entry
                            $accountTransaction = AccountTransaction::where(['supplier_id' => $purchased->supplier_id,])->get();
                            $last_closing = $accountTransaction->last()->Differentiate;
                            $description_string = 'FullCashPurchase|' . $Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_debited = $previous_entry->Debit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => 0.00,
                                    'Credit' => $previously_debited,
                                    'Differentiate' => $last_closing + $previously_debited,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'FullCashPurchase|' . $Id,
                                    'updateDescription' => 'hide',
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
                        $accountTransaction = AccountTransaction::where(['supplier_id' => $request->supplier_id,])->get();
                        // totally credit
                        if ($request->paidBalance == 0 || $request->paidBalance == 0.00) {
                            $totalCredit = $request->grandTotal;
                            $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;

                            $AccData =
                                [
                                    'supplier_id' => $request->supplier_id,
                                    'Credit' => $totalCredit,
                                    'Debit' => 0.00,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'Purchase|' . $Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                        } // partial payment some cash some credit
                        elseif ($request->paidBalance > 0 and $request->paidBalance < $request->grandTotal) {
                            $differenceValue = $accountTransaction->last()->Differentiate - $request->paidBalance;
                            $totalCredit = $accountTransaction->last()->Differentiate + $request->grandTotal;
                            $difference = $differenceValue + $request->grandTotal;

                            //make credit entry for the purchase
                            $AccData =
                                [
                                    'supplier_id' => $request->supplier_id,
                                    'Credit' => $request->grandTotal,
                                    'Debit' => 0.00,
                                    'Differentiate' => $totalCredit,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'Purchase|' . $Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);

                            //make debit entry for the whatever cash is paid
                            $difference = $totalCredit - $request->paidBalance;
                            $AccData =
                                [
                                    'supplier_id' => $request->supplier_id,
                                    'Credit' => 0.00,
                                    'Debit' => $request->paidBalance,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'PartialCashPurchase|' . $Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                        } // fully paid with cash
                        else {
                            $totalCredit = $request->grandTotal;
                            $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;

                            //make credit entry for the purchase
                            $AccountTransactions = AccountTransaction::Create([
                                'supplier_id' => $request->supplier_id,
                                'Credit' => $totalCredit,
                                'Debit' => 0.00,
                                'Differentiate' => $difference,
                                'createdDate' => $request->PurchaseDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description' => 'Purchase|' . $Id,
                            ]);

                            //make debit entry for the whatever cash is paid
                            $difference = $difference - $request->paidBalance;
                            $AccountTransactions = AccountTransaction::Create([
                                'supplier_id' => $request->supplier_id,
                                'Credit' => 0.00,
                                'Debit' => $request->paidBalance,
                                'Differentiate' => $difference,
                                'createdDate' => $request->PurchaseDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description' => 'FullCashPurchase|' . $Id,
                            ]);
                        }
                        /*new entry*/
                    } // check if only grand total is changed and not the supplier
                    elseif ($request->supplier_id == $purchased->supplier_id and $purchased->grandTotal != $request->grandTotal)
                    {
                        //supplier is not changed then need to find what is the differance in total and for payment changes
                        // here in two way we can proceed
                        // option 1 : reverse previous account entries and make new entry
                        // option 2 : find out plus minus differance and make one another entry with differences
                        // option 2 is not preferable because of while displaying we need to add or subtract similar purchase id entry so that is little tricky in query
                        // also need to manage isPaid and isPartialPaid flag according

                        // implementation of option 2
                        //case : 1 full credit entry + payment is not done yet like isPaid=0 and IsPartialPaid=0
                        if ($purchased->IsPaid == 0 && $purchased->IsPartialPaid == 0)
                        {
                            // start reverse entry
                            $last_closing = $accountTransaction->last()->Differentiate;
                            $description_string = 'Purchase|' . $Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_credited = $previous_entry->Credit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing - $previously_credited,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'Purchase|' . $Id,
                                    'updateDescription' => 'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done
                        } //case : 2 partial cash is paid and some amount is credit + payment is not fully done yet like isPaid=0 and IsPartialPaid=1
                        elseif ($purchased->paidBalance > 0 and $purchased->paidBalance < $purchased->grandTotal and $purchased->IsPartialPaid == 1)
                        {
                            // entry 1 : debit entry for purchase
                            // start reverse entry
                            $last_closing = $accountTransaction->last()->Differentiate;
                            $description_string = 'Purchase|' . $Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_credited = $previous_entry->Credit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing - $previously_credited,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'Purchase|' . $Id,
                                    'updateDescription' => 'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done
                            // entry 2 : credit whatever cash is debited
                            // start reverse entry
                            $accountTransaction = AccountTransaction::where(['supplier_id' => $purchased->supplier_id,])->get();
                            $last_closing = $accountTransaction->last()->Differentiate;
                            $description_string = 'PartialCashPurchase|' . $Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_debited = $previous_entry->Debit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => 0.00,
                                    'Credit' => $previously_debited,
                                    'Differentiate' => $last_closing + $previously_debited,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'PartialCashPurchase|' . $Id,
                                    'updateDescription' => 'hide',
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
                        } //case : 3 fully cash paid + isPaid=1 and IsPartialPaid=1
                        else
                        {
                            // entry 1 : debit entry for purchase
                            // start reverse entry
                            $last_closing = $accountTransaction->last()->Differentiate;
                            $description_string = 'Purchase|' . $Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_credited = $previous_entry->Credit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing - $previously_credited,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'Purchase|' . $Id,
                                    'updateDescription' => 'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done
                            // entry 2 : credit whatever cash is debited
                            // start reverse entry
                            $accountTransaction = AccountTransaction::where(['supplier_id' => $purchased->supplier_id,])->get();
                            $last_closing = $accountTransaction->last()->Differentiate;
                            $description_string = 'FullCashPurchase|' . $Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_debited = $previous_entry->Debit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => 0.00,
                                    'Credit' => $previously_debited,
                                    'Differentiate' => $last_closing + $previously_debited,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'FullCashPurchase|' . $Id,
                                    'updateDescription' => 'hide',
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
                        $accountTransaction = AccountTransaction::where(['supplier_id' => $request->supplier_id,])->get();
                        // totally credit
                        if ($request->paidBalance == 0 || $request->paidBalance == 0.00)
                        {
                            $totalCredit = $request->grandTotal;
                            $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;

                            $AccData =
                                [
                                    'supplier_id' => $request->supplier_id,
                                    'Credit' => $totalCredit,
                                    'Debit' => 0.00,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'Purchase|' . $Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                        } // partial payment some cash some credit
                        elseif ($request->paidBalance > 0 and $request->paidBalance < $request->grandTotal)
                        {
                            $differenceValue = $accountTransaction->last()->Differentiate - $request->paidBalance;
                            $totalCredit = $accountTransaction->last()->Differentiate + $request->grandTotal;
                            $difference = $differenceValue + $request->grandTotal;

                            //make credit entry for the purchase
                            $AccData =
                                [
                                    'supplier_id' => $request->supplier_id,
                                    'Credit' => $request->grandTotal,
                                    'Debit' => 0.00,
                                    'Differentiate' => $totalCredit,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'Purchase|' . $Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);

                            //make debit entry for the whatever cash is paid
                            $difference = $totalCredit - $request->paidBalance;
                            $AccData =
                                [
                                    'supplier_id' => $request->supplier_id,
                                    'Credit' => 0.00,
                                    'Debit' => $request->paidBalance,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'PartialCashPurchase|' . $Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                        } // fully paid with cash
                        else
                        {
                            $totalCredit = $request->grandTotal;
                            $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;

                            //make credit entry for the purchase
                            $AccountTransactions = AccountTransaction::Create([
                                'supplier_id' => $request->supplier_id,
                                'Credit' => $totalCredit,
                                'Debit' => 0.00,
                                'Differentiate' => $difference,
                                'createdDate' => $request->PurchaseDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description' => 'Purchase|' . $Id,
                            ]);

                            //make debit entry for the whatever cash is paid
                            $difference = $difference - $request->paidBalance;
                            $AccountTransactions = AccountTransaction::Create([
                                'supplier_id' => $request->supplier_id,
                                'Credit' => 0.00,
                                'Debit' => $request->paidBalance,
                                'Differentiate' => $difference,
                                'createdDate' => $request->PurchaseDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description' => 'FullCashPurchase|' . $Id,
                            ]);
                        }
                        /* new entry */
                    } // check both supplier and grandTotal is changed meaning case 3
                    elseif ($request->supplier_id != $purchased->supplier_id and $purchased->grandTotal != $request->grandTotal) {
                        //case : 1 full credit entry + payment is not done yet like isPaid=0 and IsPartialPaid=0
                        if ($purchased->IsPaid == 0 && $purchased->IsPartialPaid == 0) {
                            // start reverse entry
                            $last_closing = $accountTransaction->last()->Differentiate;
                            $description_string = 'Purchase|' . $Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_credited = $previous_entry->Credit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing - $previously_credited,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'Purchase|' . $Id,
                                    'updateDescription' => 'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done
                        } //case : 2 partial cash is paid and some amount is credit + payment is not fully done yet like isPaid=0 and IsPartialPaid=1
                        elseif ($purchased->paidBalance > 0 and $purchased->paidBalance < $purchased->grandTotal and $purchased->IsPartialPaid == 1) {
                            // entry 1 : debit entry for purchase
                            // start reverse entry
                            $last_closing = $accountTransaction->last()->Differentiate;
                            $description_string = 'Purchase|' . $Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_credited = $previous_entry->Credit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing - $previously_credited,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'Purchase|' . $Id,
                                    'updateDescription' => 'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done
                            // entry 2 : credit whatever cash is debited
                            // start reverse entry
                            $accountTransaction = AccountTransaction::where(['supplier_id' => $purchased->supplier_id,])->get();
                            $last_closing = $accountTransaction->last()->Differentiate;
                            $description_string = 'PartialCashPurchase|' . $Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_debited = $previous_entry->Debit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => 0.00,
                                    'Credit' => $previously_debited,
                                    'Differentiate' => $last_closing + $previously_debited,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'PartialCashPurchase|' . $Id,
                                    'updateDescription' => 'hide',
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
                        } //case : 3 fully cash paid + isPaid=1 and IsPartialPaid=1
                        else {
                            // entry 1 : debit entry for purchase
                            // start reverse entry
                            $last_closing = $accountTransaction->last()->Differentiate;
                            $description_string = 'Purchase|' . $Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_credited = $previous_entry->Credit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => $previously_credited,
                                    'Credit' => 0.00,
                                    'Differentiate' => $last_closing - $previously_credited,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'Purchase|' . $Id,
                                    'updateDescription' => 'hide',
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                            // reverse entry done

                            // entry 2 : credit whatever cash is debited
                            // start reverse entry
                            $accountTransaction = AccountTransaction::where(['supplier_id' => $purchased->supplier_id,])->get();
                            $last_closing = $accountTransaction->last()->Differentiate;
                            $description_string = 'FullCashPurchase|' . $Id;
                            $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                            //echo "<pre>";print_r($previous_entry->Credit);die;
                            $previously_debited = $previous_entry->Debit;
                            $AccData =
                                [
                                    'supplier_id' => $purchased->supplier_id,
                                    'Debit' => 0.00,
                                    'Credit' => $previously_debited,
                                    'Differentiate' => $last_closing + $previously_debited,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'FullCashPurchase|' . $Id,
                                    'updateDescription' => 'hide',
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
                        $accountTransaction = AccountTransaction::where(['supplier_id' => $request->supplier_id,])->get();
                        // totally credit
                        if ($request->paidBalance == 0 || $request->paidBalance == 0.00) {
                            $totalCredit = $request->grandTotal;
                            $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;

                            $AccData =
                                [
                                    'supplier_id' => $request->supplier_id,
                                    'Credit' => $totalCredit,
                                    'Debit' => 0.00,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'Purchase|' . $Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                        } // partial payment some cash some credit
                        elseif ($request->paidBalance > 0 and $request->paidBalance < $request->grandTotal) {
                            $differenceValue = $accountTransaction->last()->Differentiate - $request->paidBalance;
                            $totalCredit = $accountTransaction->last()->Differentiate + $request->grandTotal;
                            $difference = $differenceValue + $request->grandTotal;

                            //make credit entry for the purchase
                            $AccData =
                                [
                                    'supplier_id' => $request->supplier_id,
                                    'Credit' => $request->grandTotal,
                                    'Debit' => 0.00,
                                    'Differentiate' => $totalCredit,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'Purchase|' . $Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);

                            //make debit entry for the whatever cash is paid
                            $difference = $totalCredit - $request->paidBalance;
                            $AccData =
                                [
                                    'supplier_id' => $request->supplier_id,
                                    'Credit' => 0.00,
                                    'Debit' => $request->paidBalance,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->PurchaseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'PartialCashPurchase|' . $Id,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                        } // fully paid with cash
                        else {
                            $totalCredit = $request->grandTotal;
                            $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;

                            //make credit entry for the purchase
                            $AccountTransactions = AccountTransaction::Create([
                                'supplier_id' => $request->supplier_id,
                                'Credit' => $totalCredit,
                                'Debit' => 0.00,
                                'Differentiate' => $difference,
                                'createdDate' => $request->PurchaseDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description' => 'Purchase|' . $Id,
                            ]);

                            //make debit entry for the whatever cash is paid
                            $difference = $difference - $request->paidBalance;
                            $AccountTransactions = AccountTransaction::Create([
                                'supplier_id' => $request->supplier_id,
                                'Credit' => 0.00,
                                'Debit' => $request->paidBalance,
                                'Differentiate' => $difference,
                                'createdDate' => $request->PurchaseDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description' => 'FullCashPurchase|' . $Id,
                            ]);
                        }
                        /*new entry*/
                    }
                    //return Response()->json($accountTransaction);
                }
            }
            ////////////////// end of account section gautam ////////////////

            if ($purchased->IsPaid == 1 && $purchased->IsPartialPaid == 0)
            {
                $purchased->supplier_id=$request->supplier_id;
                $purchased->PurchaseDate=$request->PurchaseDate;
                $purchased->DueDate=$request->DueDate;
                $purchased->referenceNumber=$request->referenceNumber;
                $purchased->Total=$request->Total;
                $purchased->subTotal=$request->subTotal;
                $purchased->totalVat=$request->totalVat;
                $purchased->grandTotal=$request->grandTotal;

                $purchased->TermsAndCondition=$request->TermsAndCondition;
                $purchased->supplierNote=$request->supplierNote;

                $purchased->user_id=$user_id;
                $purchased->company_id=$company_id;
                $purchased->update();

                $update_note = new UpdateNote();
                $update_note->RelationTable = 'purchases';
                $update_note->RelationId = $Id;
                $update_note->Description = $request->update_note;
                $update_note->user_id = $user_id;
                $update_note->company_id = $company_id;
                $update_note->save();

                PurchaseDetail::where('purchase_id', array($Id))->delete();

                if(!empty($purchase_detail))
                {
                    foreach($purchase_detail as $purchase_item)
                    {
                        PurchaseDetail::create([
                            'purchase_id'=>$Id,
                            'PadNumber'=>$purchase_item->PadNumber,
                            'product_id'=>$purchase_item->product_id,
                            'unit_id'=>$purchase_item->unit_id,
                            'Price'=>$purchase_item->Price,
                            'Quantity'=>$purchase_item->Quantity,
                            'rowTotal'=>$purchase_item->rowTotal,
                            'VAT'=>$purchase_item->VAT,
                            'rowVatAmount'=>$purchase_item->rowVatAmount,
                            'rowSubTotal'=>$purchase_item->rowSubTotal,
                            'Description'=>$purchase_item->Description,
                        ]);
                    }
                }
                //return statement
            }
            elseif ($purchased->IsPaid == 0 && $purchased->IsPartialPaid == 1)
            {
                $purchased->supplier_id=$request->supplier_id;
                $purchased->PurchaseDate=$request->PurchaseDate;
                $purchased->DueDate=$request->DueDate;
                $purchased->referenceNumber=$request->referenceNumber;
                $purchased->Total=$request->Total;
                $purchased->subTotal=$request->subTotal;
                $purchased->totalVat=$request->totalVat;
                $purchased->grandTotal=$request->grandTotal;

                $purchased->TermsAndCondition=$request->TermsAndCondition;
                $purchased->supplierNote=$request->supplierNote;

                $purchased->user_id=$user_id;
                $purchased->company_id=$company_id;
                $purchased->update();

                $update_note = new UpdateNote();
                $update_note->RelationTable = 'purchases';
                $update_note->RelationId = $Id;
                $update_note->Description = $request->update_note;
                $update_note->user_id = $user_id;
                $update_note->company_id = $company_id;
                $update_note->save();

                PurchaseDetail::where('purchase_id', array($Id))->delete();

                if(!empty($purchase_detail))
                {
                    foreach($purchase_detail as $purchase_item)
                    {
                        PurchaseDetail::create([
                            'purchase_id'=>$Id,
                            'PadNumber'=>$purchase_item->PadNumber,
                            'product_id'=>$purchase_item->product_id,
                            'unit_id'=>$purchase_item->unit_id,
                            'Price'=>$purchase_item->Price,
                            'Quantity'=>$purchase_item->Quantity,
                            'rowTotal'=>$purchase_item->rowTotal,
                            'VAT'=>$purchase_item->VAT,
                            'rowVatAmount'=>$purchase_item->rowVatAmount,
                            'rowSubTotal'=>$purchase_item->rowSubTotal,
                            'Description'=>$purchase_item->Description,
                        ]);
                    }
                }
                // return statement
            }
            else
            {
                if ($request->paidBalance == 0.00 || $request->paidBalance == 0)
                {
                    $isPaid = 0;
                    $partialPaid = 0;
                }
                elseif ($request->paidBalance == $request->grandTotal)
                {
                    $isPaid = 1;
                    $partialPaid = 0;
                }
                else
                {
                    $isPaid = 0;
                    $partialPaid = 1;
                }

                $purchased->supplier_id=$request->supplier_id;
                $purchased->PurchaseDate=$request->PurchaseDate;
                $purchased->DueDate=$request->DueDate;
                $purchased->referenceNumber=$request->referenceNumber;
                $purchased->Total=$request->Total;
                $purchased->subTotal=$request->subTotal;
                $purchased->totalVat=$request->totalVat;
                $purchased->grandTotal=$request->grandTotal;
                $purchased->paidBalance=$request->paidBalance;
                $purchased->remainingBalance=$request->remainingBalance;
                $purchased->TermsAndCondition=$request->TermsAndCondition;
                $purchased->supplierNote=$request->supplierNote;
                $purchased->IsPaid=$isPaid;
                $purchased->IsPartialPaid=$partialPaid;
                $purchased->IsNeedStampOrSignature=$request->IsNeedStampOrSignature;
                $purchased->user_id=$user_id;
                $purchased->company_id=$company_id;
                $purchased->update();

                $update_note = new UpdateNote();
                $update_note->RelationTable = 'purchases';
                $update_note->RelationId = $Id;
                $update_note->Description = $request->update_note;
                $update_note->user_id = $user_id;
                $update_note->company_id = $company_id;
                $update_note->save();

                PurchaseDetail::where('purchase_id', array($Id))->delete();

                if(!empty($purchase_detail))
                {
                    foreach($purchase_detail as $purchase_item)
                    {
                        PurchaseDetail::create([
                            'purchase_id'=>$Id,
                            'PadNumber'=>$purchase_item->PadNumber,
                            'product_id'=>$purchase_item->product_id,
                            'unit_id'=>$purchase_item->unit_id,
                            'Price'=>$purchase_item->Price,
                            'Quantity'=>$purchase_item->Quantity,
                            'rowTotal'=>$purchase_item->rowTotal,
                            'VAT'=>$purchase_item->VAT,
                            'rowVatAmount'=>$purchase_item->rowVatAmount,
                            'rowSubTotal'=>$purchase_item->rowSubTotal,
                            'Description'=>$purchase_item->Description,
                        ]);
                    }
                }
            }
        });
        /* end of new code */

        $userId = Auth::id();
        $purchaseRequest['user_id']=$userId ?? 0;
        $purchase = Purchase::findOrFail($Id);

        ////////////////// account section ////////////////
        $accountTransaction = AccountTransaction::where(
            [
                'supplier_id'=> $purchaseRequest->supplier_id,
            ])->get();
        if (!is_null($accountTransaction)) {
            $lastAccountTransaction = $accountTransaction->Last();
            if ($lastAccountTransaction->supplier_id != $purchase->supplier_id)
            {
                if ($purchase->paidBalance == 0 || $purchase->paidBalance == 0.00) {
                    $OldValue1 = $purchase->supplier->account_transaction->Last()->Debit - $purchase->grandTotal;
                    $OldTotalDebit = $OldValue1;
                    $OldTotalCredit = $purchase->supplier->account_transaction->Last()->Credit;
                    $OldValue = $purchase->supplier->account_transaction->Last()->Differentiate + $purchase->grandTotal;
                    $OldDifference = $OldValue;
                }
                elseif ($purchase->paidBalance > 0 AND $purchase->paidBalance < $purchase->grandTotal)
                {
                    $OldTotalCredit = $purchase->supplier->account_transaction->Last()->Credit - $purchase->paidBalance;
                    $OldTotalDebit = $purchase->supplier->account_transaction->Last()->Debit - $purchase->grandTotal;
                    $differenceValue = $purchase->supplier->account_transaction->Last()->Differentiate - $purchase->paidBalance;
                    $OldDifference = $differenceValue + $purchase->grandTotal;
                }
                else{
                    $OldValue1 = $purchase->supplier->account_transaction->Last()->Credit - $purchase->paidBalance;
                    $OldTotalCredit = $OldValue1;
                    $OldTotalDebit = $purchase->supplier->account_transaction->Last()->Debit;
                    $OldValue = $purchase->supplier->account_transaction->Last()->Differentiate - $purchase->paidBalance;
                    $OldDifference = $OldValue;
                }
                $OldAccData =
                    [
                        'supplier_id' => $purchase->supplier_id,
                        'Debit' => $OldTotalDebit,
                        'Credit' => $OldTotalCredit,
                        'Differentiate' => $OldDifference,
                        'createdDate' => $purchase->supplier->account_transaction->Last()->createdDate,
                        'user_id' =>$userId,
                    ];
                $AccountTransactions = AccountTransaction::updateOrCreate([
                    'id'   => $purchase->supplier->account_transaction->Last()->id,
                ], $OldAccData);

                if ($purchaseRequest->paidBalance == 0 || $purchaseRequest->paidBalance == 0.00) {
                    $totalDebit = $lastAccountTransaction->Debit + $purchaseRequest->grandTotal;
                    $totalCredit = $lastAccountTransaction->Credit;
                    $difference = $lastAccountTransaction->Differentiate - $purchaseRequest->grandTotal;
                }
                elseif ($purchaseRequest->paidBalance > 0 AND $purchaseRequest->paidBalance < $purchaseRequest->grandTotal)
                {
                    $totalDebit = $lastAccountTransaction->Debit - $purchaseRequest->paidBalance;
                    $totalCredit = $lastAccountTransaction->Credit - $purchaseRequest->grandTotal;
                    $differenceValue = $lastAccountTransaction->last()->Differentiate - $purchaseRequest->paidBalance;
                    $difference = $differenceValue + $purchaseRequest->grandTotal;
                }
                else{
                    $totalCredit = $lastAccountTransaction->Credit + $purchaseRequest->paidBalance;
                    $totalDebit = $lastAccountTransaction->Debit;
                    $difference = $lastAccountTransaction->Differentiate + $purchaseRequest->paidBalance;
                }
            }
            else
            {
                if ($purchaseRequest->paidBalance == 0 || $purchaseRequest->paidBalance == 0.00 || $purchaseRequest->paidBalance == "") {
                    if ($lastAccountTransaction->createdDate != $purchase->supplier->account_transaction->last()->createdDate) {
                        $totalDebit = $purchaseRequest->grandTotal;
                    } else {
                        $value1 = $lastAccountTransaction->Debit - $purchase->grandTotal;
                        $totalDebit = $value1 + $purchaseRequest->grandTotal;
                    }
                    $totalCredit = $lastAccountTransaction->Credit;
                    $value = $lastAccountTransaction->Differentiate + $purchase->grandTotal;
                    $difference = $value - $purchaseRequest->grandTotal;
                }
                elseif ($purchaseRequest->paidBalance > 0 AND $purchaseRequest->paidBalance < $purchaseRequest->grandTotal)
                {

                    if ($lastAccountTransaction->createdDate != $purchase->supplier->account_transaction->last()->createdDate) {
                        $totalCredit = $purchaseRequest->paidBalance;
                        $totalDebit = $purchaseRequest->grandTotal;
                    } else {
                        $value1 = $lastAccountTransaction->Credit - $purchase->paidBalance;
                        $totalCredit = $value1 + $purchaseRequest->paidBalance;
                        $valueC = $lastAccountTransaction->Debit - $purchase->grandTotal;
                        $totalDebit = $valueC + $purchaseRequest->grandTotal;
                    }
                    $differenceValue = $lastAccountTransaction->Differentiate - $purchaseRequest->paidBalance;
                    $difference = $differenceValue + $purchaseRequest->grandTotal;
                }
                else{
                    if ($lastAccountTransaction->createdDate != $purchase->supplier->account_transaction->last()->createdDate) {
                        $totalCredit = $purchaseRequest->paidBalance;
                    } else {
                        $value1 = $lastAccountTransaction->Credit - $purchase->paidBalance;
                        $totalCredit = $value1 + $purchaseRequest->paidBalance;
                    }
                    $totalDebit = $lastAccountTransaction->Debit;
                    $value = $lastAccountTransaction->Differentiate - $purchase->paidBalance;
                    $difference = $value + $purchaseRequest->paidBalance;
                }
            }

            $AccData =
                [
                    'supplier_id' => $purchaseRequest->supplier_id,
                    'Credit' => $totalCredit,
                    'Debit' => $totalDebit,
                    'Differentiate' => $difference,
                    'createdDate' => $lastAccountTransaction->createdDate,
                    'user_id' =>$userId,
                ];
            $AccountTransactions = AccountTransaction::updateOrCreate([
                'createdDate'   => $lastAccountTransaction->createdDate,
                'id'   => $lastAccountTransaction->id,
            ], $AccData);
            //return Response()->json($accountTransaction);
        }
        ////////////////// end of account section ////////////////

        if ($purchaseRequest->paidBalance == 0.00 || $purchaseRequest->paidBalance == 0)
        {
            $isPaid = false;
            $partialPaid =false;
        }
        elseif($purchaseRequest->paidBalance >= $purchaseRequest->grandTotal)
        {
            $isPaid = true;
            $partialPaid =false;
        }
        else
        {
            $isPaid = false;
            $partialPaid =true;
        }

        $purchase->supplier_id=$purchaseRequest->supplier_id;
        $purchase->employee_id=$purchaseRequest->employee_id;
        $purchase->PurchaseDate=$purchaseRequest->PurchaseDate;
        $purchase->DueDate=$purchaseRequest->DueDate;
        $purchase->referenceNumber=$purchaseRequest->referenceNumber;
        $purchase->Total=$purchaseRequest->Total;
        $purchase->subTotal=$purchaseRequest->subTotal;
        $purchase->totalVat=$purchaseRequest->totalVat;
        $purchase->grandTotal=$purchaseRequest->grandTotal;
        $purchase->paidBalance=$purchaseRequest->paidBalance;
        $purchase->remainingBalance=$purchaseRequest->remainingBalance;
        $purchase->Description=$purchaseRequest->Description;
        $purchase->TermsAndCondition=$purchaseRequest->TermsAndCondition;
        $purchase->supplierNote=$purchaseRequest->supplierNote;
        $purchase->IsPaid=$isPaid;
        $purchase->IsPartialPaid=$partialPaid;
        $purchase->IsNeedStampOrSignature=$purchaseRequest->IsNeedStampOrSignature;
        $purchase->update();

        $update_note = new UpdateNote();
        $update_note->RelationTable = 'purchases';
        $update_note->RelationId = $Id;
        $update_note->Description = $purchaseRequest->update_note;
        $update_note->user_id = $userId;
        $update_note->save();

        PurchaseDetail::where('purchase_id', array($Id))->delete();
        //DB::table('purchase_details')->where([['purchase_id', $Id]])->delete();
        $purchase_detail=json_decode($_POST['pd']);
        if(!empty($purchase_detail))
        {
            foreach ($purchase_detail as $purchase_item)
            {
                PurchaseDetail::create([
                    'purchase_id'=>$Id,
                    'PadNumber'=>$purchase_item->PadNumber,
                    'product_id'=>$purchase_item->product_id,
                    'unit_id'=>$purchase_item->unit_id,
                    'Price'=>$purchase_item->Price,
                    'Quantity'=>$purchase_item->Quantity,
                    'rowTotal'=>$purchase_item->rowTotal,
                    'VAT'=>$purchase_item->VAT,
                    'rowVatAmount'=>$purchase_item->rowVatAmount,
                    'rowSubTotal'=>$purchase_item->rowSubTotal,
                    'Description'=>$purchase_item->Description,
                ]);
            }
        }
        $Response = PurchaseResource::collection(Purchase::where('id',$Id)->with('user','supplier','purchase_details')->get());
        $data = json_decode(json_encode($Response), true);
        return $data[0];
    }

    public function getById($Id)
    {
        $Response = PurchaseResource::collection(Purchase::where('id',$Id)->with('user','supplier','purchase_details','update_notes','documents')->get());
        $data = json_decode(json_encode($Response), true);
        return $data[0];
    }

    public function BaseList()
    {
        $user_id = Auth::id();
        $company_id=Str::getCompany($user_id);
        return array('pad_number'=>$this->PadNumber(),'products'=>ProductResource::collection(Product::all('id','Name','updated_at')->sortDesc()),'supplier'=>Supplier::select('id','Name','Address','Mobile','postCode','TRNNumber')->where('company_type_id',2)->where('company_id',$company_id)->orderBy('id','desc')->get());
    }

    public function PadNumber()
    {
        $user_id = Auth::id();
        $company_id=Str::getCompany($user_id);
        $max_purchase_id = PurchaseDetail::where('company_id',$company_id)->max('id');
        $max_purchase_id = PurchaseDetail::where('id',$max_purchase_id)->first();
        if($max_purchase_id)
        {
            $lastPad = $max_purchase_id->PadNumber;
            if(!is_numeric($lastPad))
            {
                $newPad=1;
            }
            else
            {
                $newPad = ($lastPad + 1);
            }
        }
        else
        {
            $newPad=1;
        }
        return $newPad;
    }

    public function delete(Request $request, $Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = Purchase::find($Id);
        $update->user_id=$userId;
        $update->save();
        $purchase = Purchase::withoutTrashed()->find($Id);
        if($purchase->trashed())
        {
            return new PurchaseResource(Purchase::onlyTrashed()->find($Id));
        }
        else
        {
            DB::table('purchase_details')->where([['purchase_id', $Id]])->update(['deleted_at' =>date('Y-m-d h:i:s')]);
            $purchase->delete();
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

    public function PurchaseDocumentsUpload(Request $request)
    {
        try
        {
            $userId = Auth::id();
            if ($request->hasfile('document'))
            {
                foreach($request->file('document') as $document)
                {
                    $extension = $document->getClientOriginalExtension();
                    $filename=uniqid('purchase_doc_'.$request->id.'_').'.'.$extension;
                    $document->storeAs('document/',$filename,'public');

                    $file_upload = new FileUpload();
                    $file_upload->Title = $filename;
                    $file_upload->RelationTable = 'purchases';
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
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return $this->userResponse->Failed($purchase = (object)[],'Not Found.');
        }
    }

    public function supplierPurchaseDetails($Id)
    {
        $purchase = Purchase::with('supplier','purchase_details')
            ->where([
                'supplier_id'=>$Id,
                'IsPaid'=> false,
            ])->get();
        return $purchase;
    }
}
