<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\IExpenseRepositoryInterface;
use App\Http\Requests\ExpenseRequest;
use App\Http\Resources\Expense\ExpenseResource;
use App\Models\AccountTransaction;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\CashTransaction;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseDetail;
use App\Models\FileUpload;
use App\Models\PaymentType;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\UpdateNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PDF;

class ExpenseRepository implements IExpenseRepositoryInterface
{

    public function all()
    {
        return ExpenseResource::collection(Expense::with('user','expense_details')->get()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return ExpenseResource::Collection(Expense::with('user','expense_details','update_notes')->get()->sortDesc()->forPage($page_no,$page_size));
    }

    public function ActivateDeactivate($Id)
    {
        $expense = Expense::find($Id);
        if($expense->isActive==1)
        {
            $expense->isActive=0;
        }
        else
        {
            $expense->isActive=1;
        }
        $expense->update();
        return new ExpenseResource(Expense::find($Id));
    }

    public function invoiceNumber()
    {
        $invoice = new Expense();
        $lastInvoiceID = $invoice->orderByDesc('id')->pluck('id')->first();
        $newInvoiceID = 'EXP-00'.($lastInvoiceID + 1);
        return $newInvoiceID;
    }

    public function insert(Request $request)
    {
        $expense_id=0;
        /* start new code */
        DB::transaction(function () use($request,&$expense_id)
        {
            $user_id = Auth::id();
            $company_id=Str::getCompany($user_id);

            $expense = new Expense();
            $expense->expenseNumber = $this->invoiceNumber();
            $expense->referenceNumber = $request->referenceNumber;
            $expense->expenseDate = $request->expenseDate;
            $expense->Total = $request->Total;
            $expense->subTotal = $request->subTotal;
            $expense->totalVat = $request->totalVat;
            $expense->grandTotal = $request->grandTotal;
            $expense->paidBalance = $request->grandTotal;
            $expense->remainingBalance = 0.00;

            $expense->payment_type = $request->payment_type;
            if($request->payment_type!='cash')
            {
                $expense->bank_id = $request->bank_id;
                $expense->accountNumber = $request->accountNumber;
                $expense->transferDate = $request->transferDate;
                $expense->ChequeNumber = $request->ChequeNumber;
            }
            $expense->supplier_id = $request->supplier_id;
            $expense->employee_id = $request->employee_id;
            $expense->user_id = $user_id;
            $expense->company_id = $company_id;
            $expense->save();
            $expense = $expense->id;
            $expense_id=$expense;

            $expense_detail=json_decode($_POST['expense_detail']);

            foreach ($expense_detail as $expense_item)
            {
                ExpenseDetail::create([
                    'expense_id'=>$expense,
                    'expense_category_id'=>$expense_item->expense_category_id,
                    'expenseDate'=>$expense_item->expenseDate,
                    'PadNumber'=>$expense_item->PadNumber,
                    'Description'=>$expense_item->Description,
                    'Total'=>$expense_item->Total,
                    'VAT'=>$expense_item->VAT,
                    'rowVatAmount'=>$expense_item->rowVatAmount,
                    'rowSubTotal'=>$expense_item->rowSubTotal,
                    'user_id'=>$user_id,
                    'company_id'=>$company_id,
                ]);
            }

            $accountDescriptionString='';
            if($request->payment_type=='cash')
            {
                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                $difference = $cashTransaction->last()->Differentiate;
                $cash_transaction = new CashTransaction();
                $cash_transaction->Reference=$expense;
                $cash_transaction->createdDate=$request->expenseDate;
                $cash_transaction->Type='expenses';
                $cash_transaction->Details='CashExpense|'.$expense;
                $cash_transaction->Credit=$request->grandTotal;
                $cash_transaction->Debit=0.00;
                $cash_transaction->Differentiate=$difference-$request->grandTotal;
                $cash_transaction->user_id = $user_id;
                $cash_transaction->company_id = $company_id;
                $cash_transaction->PadNumber = $request->referenceNumber;
                $cash_transaction->save();

                $accountDescriptionString='CashExpense|';
            }
            else
            {
                if($request->payment_type=='bank')
                {
                    $bankTransaction = BankTransaction::where(['bank_id'=> $request->bank_id])->get();
                    $difference = $bankTransaction->last()->Differentiate;
                    $bank_transaction = new BankTransaction();
                    $bank_transaction->Reference=$expense;
                    $bank_transaction->createdDate=$request->transferDate ?? date('Y-m-d h:i:s');
                    $bank_transaction->Type='expenses';
                    $bank_transaction->Details='BankTransferExpense|'.$expense;
                    $bank_transaction->Credit=$request->grandTotal;
                    $bank_transaction->Debit=0.00;
                    $bank_transaction->Differentiate=$difference-$request->grandTotal;
                    $bank_transaction->user_id = $user_id;
                    $bank_transaction->company_id = $company_id;
                    $bank_transaction->bank_id = $request->bank_id;
                    $bank_transaction->updateDescription = $request->ChequeNumber;
                    $bank_transaction->save();

                    $accountDescriptionString='BankTransferExpense|';
                }
                elseif($request->payment_type=='cheque')
                {
                    $bankTransaction = BankTransaction::where(['bank_id'=> $request->bank_id])->get();
                    $difference = $bankTransaction->last()->Differentiate;
                    $bank_transaction = new BankTransaction();
                    $bank_transaction->Reference=$expense;
                    $bank_transaction->createdDate=$request->transferDate ?? date('Y-m-d h:i:s');
                    $bank_transaction->Type='expenses';
                    $bank_transaction->Details='ChequeExpense|'.$expense;
                    $bank_transaction->Credit=$request->grandTotal;
                    $bank_transaction->Debit=0.00;
                    $bank_transaction->Differentiate=$difference-$request->grandTotal;
                    $bank_transaction->user_id = $user_id;
                    $bank_transaction->company_id = $company_id;
                    $bank_transaction->bank_id = $request->bank_id;
                    $bank_transaction->updateDescription = $request->ChequeNumber;
                    $bank_transaction->save();

                    $accountDescriptionString='ChequeExpense|';
                }
            }

            ////////////////// start account section gautam ////////////////
            if ($expense)
            {
                $accountTransaction = AccountTransaction::where(['supplier_id'=> $request->supplier_id,])->get();

                // fully paid with cash or bank

                $totalCredit = $request->grandTotal;
                $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;

                //make credit entry for the expense
                $AccountTransactions=AccountTransaction::Create([
                    'supplier_id' => $request->supplier_id,
                    'Credit' => $totalCredit,
                    'Debit' => 0.00,
                    'Differentiate' => $difference,
                    'createdDate' => $request->expenseDate,
                    'user_id' => $user_id,
                    'company_id' => $company_id,
                    'Description'=>'Expense|'.$expense,
                    'referenceNumber'=>$request->referenceNumber,
                ]);

                //make debit entry for the whatever cash or bank account is credited
                $difference=$difference-$request->grandTotal;
                $AccountTransactions=AccountTransaction::Create([
                    'supplier_id' => $request->supplier_id,
                    'Credit' => 0.00,
                    'Debit' => $request->grandTotal,
                    'Differentiate' => $difference,
                    'createdDate' => $request->expenseDate,
                    'user_id' => $user_id,
                    'company_id' => $company_id,
                    'referenceNumber'=>$request->referenceNumber ?? '',
                    'Description'=>$accountDescriptionString.$expense,
                    'updateDescription'=>$request->ChequeNumber ?? '',
                ]);
            }
            ////////////////// end account section gautam ////////////////
        });
        /* end new code */

        /*$expense = new Expense();
        $lastExpenseID = $expense->orderByDesc('id')->pluck('id')->first();
        $newExpenseID = 'EXP-00'.($lastExpenseID + 1);

        //$expense_detail=$request->expense_detail;

        $userId = Auth::id();
        $company_id=Str::getCompany($userId);
        $expense = new Expense();
        $expense->expenseNumber=$newExpenseID;
        $expense->user_id = $userId ?? 0;
        $expense->company_id = 0;
        $expense->employee_id=$request->employee_id;
        $expense->supplier_id=$request->supplier_id;
        $expense->expenseDate=$request->expenseDate;
        $expense->referenceNumber=$request->referenceNumber;
        $expense->Total=$request->Total;
        $expense->subTotal=$request->subTotal;
        $expense->totalVat=$request->totalVat;
        $expense->grandTotal=$request->grandTotal;
        $expense->Description=$request->Description;
        //$expense->termsAndCondition=$request->termsAndCondition;
        $expense->paidBalance=$request->paidBalance;
        $expense->remainingBalance=$request->remainingBalance;
        $expense->supplierNote=$request->supplierNote;
        $expense->createdDate=date('Y-m-d h:i:s');
        $expense->isActive=1;
        $expense->user_id = $userId ?? 0;
        $expense->company_id=$company_id;

        $expense->save();
        $expense_id = $expense->id;


        $expense_detail=json_decode($_POST['expense_detail']);

        foreach ($expense_detail as $expense_item)
        {
            $data=ExpenseDetail::create([
                'expense_id'=>$expense_id,
                'expense_category_id'=>$expense_item->expense_category_id,
                'expenseDate'=>$expense_item->expenseDate,
                'PadNumber'=>$expense_item->PadNumber,
                'Description'=>$expense_item->Description,
                'Total'=>$expense_item->Total,
                'VAT'=>$expense_item->VAT,
                'rowVatAmount'=>$expense_item->rowVatAmount,
                'rowSubTotal'=>$expense_item->rowSubTotal,
                'user_id'=>$userId,
                'company_id'=>$company_id,
            ]);
        }*/

        $Response = ExpenseResource::collection(Expense::where('id',$expense_id)->with('user','supplier','expense_details')->get());
        $data = json_decode(json_encode($Response), true);
        return $data[0];
    }

    public function update(Request $request, $Id)
    {
        /* start new code */
        DB::transaction(function () use($request,$Id)
        {
            $expensed = Expense::find($Id);
            $user_id = Auth::id();
            $company_id=Str::getCompany($user_id);

            ////////////////// account section gautam ////////////////
            $accountTransaction = AccountTransaction::where(['supplier_id'=> $expensed->supplier_id,])->get();
            if (!is_null($accountTransaction))
            {
                // identify only and only payment method is changing
                if($expensed->payment_type!=$request->payment_type && $expensed->supplier_id==$request->supplier_id && $expensed->grandTotal==$request->grandTotal)
                {
                    // start reverse entry for wrong payment method
                    if($expensed->payment_type=='cash')
                    {
                        $description_string='CashExpense|'.$Id;

                        $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                        $difference = $cashTransaction->last()->Differentiate;
                        $cash_transaction = new CashTransaction();
                        $cash_transaction->Reference=$Id;
                        $cash_transaction->createdDate=$request->expenseDate;
                        $cash_transaction->Type='expenses';
                        $cash_transaction->Details='CashExpenseReversal|'.$Id;
                        $cash_transaction->Credit=0.00;
                        $cash_transaction->Debit=$request->grandTotal;
                        $cash_transaction->Differentiate=$difference+$request->grandTotal;
                        $cash_transaction->user_id = $user_id;
                        $cash_transaction->company_id = $company_id;
                        $cash_transaction->PadNumber = $expensed->referenceNumber;
                        $cash_transaction->save();
                    }
                    elseif($expensed->payment_type=='bank')
                    {
                        $description_string='BankTransferExpense|'.$Id;

                        $bankTransaction = BankTransaction::where(['bank_id'=> $expensed->bank_id])->get();
                        $difference = $bankTransaction->last()->Differentiate;
                        $bank_transaction = new BankTransaction();
                        $bank_transaction->Reference=$Id;
                        $bank_transaction->createdDate=$request->transferDate ?? date('Y-m-d h:i:s');
                        $bank_transaction->Type='expenses';
                        $bank_transaction->Details='BankTransferExpenseReversal|'.$Id;
                        $bank_transaction->Credit=0.00;
                        $bank_transaction->Debit=$request->grandTotal;
                        $bank_transaction->Differentiate=$difference+$request->grandTotal;
                        $bank_transaction->user_id = $user_id;
                        $bank_transaction->company_id = $company_id;
                        $bank_transaction->bank_id = $expensed->bank_id;
                        $bank_transaction->updateDescription = $expensed->referenceNumber;
                        $bank_transaction->save();
                    }
                    elseif($expensed->payment_type=='cheque')
                    {
                        $description_string='ChequeExpense|'.$Id;

                        $bankTransaction = BankTransaction::where(['bank_id'=> $expensed->bank_id])->get();
                        $difference = $bankTransaction->last()->Differentiate;
                        $bank_transaction = new BankTransaction();
                        $bank_transaction->Reference=$Id;
                        $bank_transaction->createdDate=$request->transferDate ?? date('Y-m-d h:i:s');
                        $bank_transaction->Type='expenses';
                        $bank_transaction->Details='ChequeExpenseReversal|'.$Id;
                        $bank_transaction->Credit=0.00;
                        $bank_transaction->Debit=$request->grandTotal;
                        $bank_transaction->Differentiate=$difference+$request->grandTotal;
                        $bank_transaction->user_id = $user_id;
                        $bank_transaction->company_id = $company_id;
                        $bank_transaction->bank_id = $expensed->bank_id;
                        $bank_transaction->updateDescription = $expensed->referenceNumber;
                        $bank_transaction->save();
                    }
                    $previous_entry = AccountTransaction::get()->where('company_id','=',$company_id)->where('supplier_id','=',$expensed->supplier_id)->where('Description','like',$description_string)->last();
                    if($previous_entry)
                    {
                        $new_description_string='';
                        $new_update_description='';
                        if($request->payment_type=='cash')
                        {
                            $new_description_string='CashExpense|'.$Id;
                            $new_update_description=$request->referenceNumber;

                            $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                            $difference = $cashTransaction->last()->Differentiate;
                            $cash_transaction = new CashTransaction();
                            $cash_transaction->Reference=$Id;
                            $cash_transaction->createdDate=$request->expenseDate;
                            $cash_transaction->Type='expenses';
                            $cash_transaction->Details='CashExpense|'.$Id;
                            $cash_transaction->Credit=$request->grandTotal;
                            $cash_transaction->Debit=0.00;
                            $cash_transaction->Differentiate=$difference-$request->grandTotal;
                            $cash_transaction->user_id = $user_id;
                            $cash_transaction->company_id = $company_id;
                            $cash_transaction->PadNumber = $request->referenceNumber;
                            $cash_transaction->save();
                        }
                        elseif($request->payment_type=='bank')
                        {
                            $new_description_string='BankTransferExpense|'.$Id;
                            $new_update_description=$request->ChequeNumber;

                            $bankTransaction = BankTransaction::where(['bank_id'=> $request->bank_id])->get();
                            $difference = $bankTransaction->last()->Differentiate;
                            $bank_transaction = new BankTransaction();
                            $bank_transaction->Reference=$Id;
                            $bank_transaction->createdDate=$request->transferDate ?? date('Y-m-d h:i:s');
                            $bank_transaction->Type='expenses';
                            $bank_transaction->Details='BankTransferExpense|'.$Id;
                            $bank_transaction->Credit=$request->grandTotal;
                            $bank_transaction->Debit=0.00;
                            $bank_transaction->Differentiate=$difference-$request->grandTotal;
                            $bank_transaction->user_id = $user_id;
                            $bank_transaction->company_id = $company_id;
                            $bank_transaction->bank_id = $request->bank_id;
                            $bank_transaction->updateDescription = $request->ChequeNumber;
                            $bank_transaction->save();
                        }
                        elseif($request->payment_type=='cheque')
                        {
                            $new_description_string='ChequeExpense|'.$Id;
                            $new_update_description=$request->ChequeNumber;

                            $bankTransaction = BankTransaction::where(['bank_id'=> $request->bank_id])->get();
                            $difference = $bankTransaction->last()->Differentiate;
                            $bank_transaction = new BankTransaction();
                            $bank_transaction->Reference=$Id;
                            $bank_transaction->createdDate=$request->transferDate ?? date('Y-m-d h:i:s');
                            $bank_transaction->Type='expenses';
                            $bank_transaction->Details='ChequeExpense|'.$Id;
                            $bank_transaction->Credit=$request->grandTotal;
                            $bank_transaction->Debit=0.00;
                            $bank_transaction->Differentiate=$difference-$request->grandTotal;
                            $bank_transaction->user_id = $user_id;
                            $bank_transaction->company_id = $company_id;
                            $bank_transaction->bank_id = $request->bank_id;
                            $bank_transaction->updateDescription = $request->ChequeNumber;
                            $bank_transaction->save();
                        }
                        $previous_entry->update(
                            [
                                'Description' => $new_description_string,
                                'updateDescription' => $new_update_description,
                            ]);
                    }
                }
                // identify only payment method is not changing
                elseif($expensed->payment_type!=$request->payment_type || $expensed->supplier_id!=$request->supplier_id || $expensed->grandTotal!=$request->grandTotal)
                {
                    $description_string='Expense|'.$Id;
                    $previous_entry = AccountTransaction::get()->where('supplier_id','=',$expensed->supplier_id)->where('Description','like',$description_string)->last();
                    $last_closing = $accountTransaction->last()->Differentiate;
                    $previously_credited = $previous_entry->Credit;
                    $AccData =
                        [
                            'supplier_id' => $expensed->supplier_id,
                            'Debit' => $previously_credited,
                            'Credit' => 0.00,
                            'Differentiate' => $last_closing-$previously_credited,
                            'createdDate' => $request->expenseDate,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'Expense|'.$Id,
                            'updateDescription'=>'hide',
                            'referenceNumber'=>$expensed->referenceNumber,
                        ];
                    $AccountTransactions = AccountTransaction::Create($AccData);
                    // also hide previous entry start
                    AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                    // also hide previous entry end

                    if($expensed->payment_type=='cash')
                    {
                        $description_string='CashExpense|'.$Id;

                        $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                        $difference = $cashTransaction->last()->Differentiate;
                        $cash_transaction = new CashTransaction();
                        $cash_transaction->Reference=$Id;
                        $cash_transaction->createdDate=$expensed->expenseDate;
                        $cash_transaction->Type='expenses';
                        $cash_transaction->Details='CashExpenseReversal|'.$Id;
                        $cash_transaction->Credit=0.00;
                        $cash_transaction->Debit=$expensed->grandTotal;
                        $cash_transaction->Differentiate=$difference+$expensed->grandTotal;
                        $cash_transaction->user_id = $user_id;
                        $cash_transaction->company_id = $company_id;
                        $cash_transaction->PadNumber = $request->referenceNumber;
                        $cash_transaction->save();

                        $previous_entry = AccountTransaction::get()->where('supplier_id','=',$expensed->supplier_id)->where('Description','like',$description_string)->last();
                        $previously_debited = $previous_entry->Debit;
                        $accountTransaction = AccountTransaction::where(['supplier_id'=> $expensed->supplier_id,])->get();
                        $last_closing = $accountTransaction->last()->Differentiate;
                        $AccData =
                            [
                                'supplier_id' => $expensed->supplier_id,
                                'Debit' => 0.00,
                                'Credit' => $previously_debited,
                                'Differentiate' => $last_closing+$previously_debited,
                                'createdDate' => $request->expenseDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'CashExpense|'.$Id,
                                'updateDescription'=>'hide',
                                'referenceNumber'=>$expensed->referenceNumber,
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // also hide previous entry start
                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                        // also hide previous entry end
                    }
                    elseif($expensed->payment_type=='bank')
                    {
                        $description_string='BankTransferExpense|'.$Id;

                        $bankTransaction = BankTransaction::where(['bank_id'=> $request->bank_id])->get();
                        $difference = $bankTransaction->last()->Differentiate;
                        $bank_transaction = new BankTransaction();
                        $bank_transaction->Reference=$Id;
                        $bank_transaction->createdDate=$expensed->transferDate ?? date('Y-m-d h:i:s');
                        $bank_transaction->Type='expenses';
                        $bank_transaction->Details='BankTransferExpenseReversal|'.$Id;
                        $bank_transaction->Credit=0.00;
                        $bank_transaction->Debit=$expensed->grandTotal;
                        $bank_transaction->Differentiate=$difference+$expensed->grandTotal;
                        $bank_transaction->user_id = $user_id;
                        $bank_transaction->company_id = $company_id;
                        $bank_transaction->bank_id = $expensed->bank_id;
                        $bank_transaction->updateDescription = '';
                        $bank_transaction->save();

                        $previous_entry = AccountTransaction::get()->where('supplier_id','=',$expensed->supplier_id)->where('Description','like',$description_string)->last();
                        $previously_debited = $previous_entry->Debit;
                        $accountTransaction = AccountTransaction::where(['supplier_id'=> $expensed->supplier_id,])->get();
                        $last_closing = $accountTransaction->last()->Differentiate;
                        $AccData =
                            [
                                'supplier_id' => $expensed->supplier_id,
                                'Debit' => 0.00,
                                'Credit' => $previously_debited,
                                'Differentiate' => $last_closing+$previously_debited,
                                'createdDate' => $request->expenseDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'BankTransferExpense|'.$Id,
                                'updateDescription'=>'hide',
                                'referenceNumber'=>$expensed->referenceNumber,

                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // also hide previous entry start
                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                        // also hide previous entry end
                    }
                    elseif($expensed->payment_type=='cheque')
                    {
                        $description_string='ChequeExpense|'.$Id;

                        $bankTransaction = BankTransaction::where(['bank_id'=> $request->bank_id])->get();
                        $difference = $bankTransaction->last()->Differentiate;
                        $bank_transaction = new BankTransaction();
                        $bank_transaction->Reference=$Id;
                        $bank_transaction->createdDate=$expensed->transferDate ?? date('Y-m-d h:i:s');
                        $bank_transaction->Type='expenses';
                        $bank_transaction->Details='ChequeExpenseReversal|'.$Id;
                        $bank_transaction->Credit=0.00;
                        $bank_transaction->Debit=$expensed->grandTotal;
                        $bank_transaction->Differentiate=$difference+$expensed->grandTotal;
                        $bank_transaction->user_id = $user_id;
                        $bank_transaction->company_id = $company_id;
                        $bank_transaction->bank_id = $expensed->bank_id;
                        $bank_transaction->updateDescription = $expensed->referenceNumber;
                        $bank_transaction->save();

                        $previous_entry = AccountTransaction::get()->where('supplier_id','=',$expensed->supplier_id)->where('Description','like',$description_string)->last();
                        $previously_debited = $previous_entry->Debit;
                        $accountTransaction = AccountTransaction::where(['supplier_id'=> $expensed->supplier_id,])->get();
                        $last_closing = $accountTransaction->last()->Differentiate;
                        $AccData =
                            [
                                'supplier_id' => $expensed->supplier_id,
                                'Debit' => 0.00,
                                'Credit' => $previously_debited,
                                'Differentiate' => $last_closing+$previously_debited,
                                'createdDate' => $request->expenseDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'ChequeExpense|'.$Id,
                                'updateDescription'=>'hide',
                                'referenceNumber'=>$expensed->referenceNumber,
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // also hide previous entry start
                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                        // also hide previous entry end
                    }

                    // new entry start
                    $accountTransaction = AccountTransaction::where(['supplier_id'=> $request->supplier_id,])->get();
                    $difference = $accountTransaction->last()->Differentiate + $request->grandTotal;
                    $AccData =
                        [
                            'supplier_id' => $request->supplier_id,
                            'Credit' => $request->grandTotal,
                            'Debit' => 0.00,
                            'Differentiate' => $difference,
                            'createdDate' => $request->expenseDate,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'Expense|'.$Id,
                            'referenceNumber'=>$request->referenceNumber,
                        ];
                    $AccountTransactions = AccountTransaction::Create($AccData);

                    $new_description_string='';
                    $new_update_description='';
                    if($request->payment_type=='cash')
                    {
                        $new_description_string='CashExpense|'.$Id;
                        $new_update_description=$request->referenceNumber;

                        $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                        $difference = $cashTransaction->last()->Differentiate;
                        $cash_transaction = new CashTransaction();
                        $cash_transaction->Reference=$Id;
                        $cash_transaction->createdDate=$request->expenseDate;
                        $cash_transaction->Type='expenses';
                        $cash_transaction->Details='CashExpense|'.$Id;
                        $cash_transaction->Credit=$request->grandTotal;
                        $cash_transaction->Debit=0.00;
                        $cash_transaction->Differentiate=$difference-$request->grandTotal;
                        $cash_transaction->user_id = $user_id;
                        $cash_transaction->company_id = $company_id;
                        $cash_transaction->PadNumber = $request->referenceNumber;
                        $cash_transaction->save();
                    }
                    elseif($request->payment_type=='bank')
                    {
                        $new_description_string='BankTransferExpense|'.$Id;
                        $new_update_description=$request->ChequeNumber;

                        $bankTransaction = BankTransaction::where(['bank_id'=> $request->bank_id])->get();
                        $difference = $bankTransaction->last()->Differentiate;
                        $bank_transaction = new BankTransaction();
                        $bank_transaction->Reference=$Id;
                        $bank_transaction->createdDate=$request->transferDate ?? date('Y-m-d h:i:s');
                        $bank_transaction->Type='expenses';
                        $bank_transaction->Details='BankTransferExpense|'.$Id;
                        $bank_transaction->Credit=$request->grandTotal;
                        $bank_transaction->Debit=0.00;
                        $bank_transaction->Differentiate=$difference-$request->grandTotal;
                        $bank_transaction->user_id = $user_id;
                        $bank_transaction->company_id = $company_id;
                        $bank_transaction->bank_id = $request->bank_id;
                        $bank_transaction->updateDescription = $request->ChequeNumber;
                        $bank_transaction->save();
                    }
                    elseif($request->payment_type=='cheque')
                    {
                        $new_description_string='ChequeExpense|'.$Id;
                        $new_update_description=$request->ChequeNumber;

                        $bankTransaction = BankTransaction::where(['bank_id'=> $request->bank_id])->get();
                        $difference = $bankTransaction->last()->Differentiate;
                        $bank_transaction = new BankTransaction();
                        $bank_transaction->Reference=$Id;
                        $bank_transaction->createdDate=$request->transferDate ?? date('Y-m-d h:i:s');
                        $bank_transaction->Type='expenses';
                        $bank_transaction->Details='ChequeExpense|'.$Id;
                        $bank_transaction->Credit=$request->grandTotal;
                        $bank_transaction->Debit=0.00;
                        $bank_transaction->Differentiate=$difference-$request->grandTotal;
                        $bank_transaction->user_id = $user_id;
                        $bank_transaction->company_id = $company_id;
                        $bank_transaction->bank_id = $request->bank_id;
                        $bank_transaction->updateDescription = $request->ChequeNumber;
                        $bank_transaction->save();
                    }
                    //make debit entry for the whatever cash or bank account is credited
                    $accountTransaction = AccountTransaction::where(['supplier_id'=> $request->supplier_id,])->get();
                    $difference = $accountTransaction->last()->Differentiate - $request->grandTotal;
                    $AccountTransactions=AccountTransaction::Create([
                        'supplier_id' => $request->supplier_id,
                        'Credit' => 0.00,
                        'Debit' => $request->grandTotal,
                        'Differentiate' => $difference,
                        'createdDate' => $request->expenseDate,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description'=>$new_description_string,
                        'updateDescription'=>$new_update_description ?? '',
                        'referenceNumber'=>$new_update_description ?? '',
                    ]);

                    //new entry end
                }
            }
            ////////////////// end of account section gautam ////////////////

            //here will come cash transaction record update if scenario will come by
            $bank_id=0;
            $accountNumber=NULL;
            $transferDate=$request->expenseDate;
            $ChequeNumber=NULL;
            if($request->payment_type!='cash')
            {
                $bank_id=$request->bank_id;
                $accountNumber=$request->accountNumber;
                $transferDate=$request->transferDate;
                $ChequeNumber=$request->ChequeNumber;
            }
            $expensed->update(
                [
                    'referenceNumber' => $request->referenceNumber,
                    'expenseDate' => $request->expenseDate,
                    'Total' => $request->Total,
                    'subTotal' => $request->subTotal,
                    'totalVat' => $request->totalVat,
                    'grandTotal' => $request->grandTotal,
                    'paidBalance' => $request->grandTotal,
                    'remainingBalance' => 0.00,
                    'payment_type' => $request->payment_type,
                    'bank_id' => $bank_id,
                    'accountNumber' => $accountNumber,
                    'transferDate' => $transferDate,
                    'ChequeNumber' => $ChequeNumber,
                    'supplier_id' => $request->supplier_id,
                    'employee_id' => $request->employee_id,
                    'user_id' => $user_id,
                    'company_id' => $company_id,
                ]);

            $update_note = new UpdateNote();
            $update_note->RelationTable = 'expenses';
            $update_note->RelationId = $Id;
            $update_note->Description = $request->update_note;
            $update_note->user_id = $user_id;
            $update_note->save();

            ExpenseDetail::where('expense_id', array($Id))->delete();

            $expense_detail=json_decode($_POST['expense_detail']);

            if(!empty($expense_detail))
            {
                foreach ($expense_detail as $expense_item)
                {
                    ExpenseDetail::create([
                        'expense_id'=>$Id,
                        'expense_category_id'=>$expense_item->expense_category_id,
                        'expenseDate'=>$expense_item->expenseDate,
                        'PadNumber'=>$expense_item->PadNumber,
                        'Description'=>$expense_item->Description,
                        'Total'=>$expense_item->Total,
                        'VAT'=>$expense_item->VAT,
                        'rowVatAmount'=>$expense_item->rowVatAmount,
                        'rowSubTotal'=>$expense_item->rowSubTotal,
                        'user_id'=>$user_id,
                        'company_id'=>$company_id,
                    ]);
                }
            }
        });
        /* end new code */

        $Response = ExpenseResource::collection(Expense::where('id',$Id)->with('user','supplier','expense_details')->get());
        $data = json_decode(json_encode($Response), true);
        return $data[0];
    }

    public function getById($Id)
    {
        $Response = ExpenseResource::collection(Expense::where('id',$Id)->with('user','supplier','expense_details','update_notes','documents')->get());
        $data = json_decode(json_encode($Response), true);
        return $data[0];
    }

    public function ExpenseSearchByRef(Request $request)
    {
        $Response = ExpenseResource::collection(Expense::where('referenceNumber','LIKE',"%{$request->referenceNumber}%")->with('user','supplier','expense_details','update_notes','documents')->get());
        $data = json_decode(json_encode($Response), true);
        return $data;
    }

    public function PadNumber()
    {
        $user_id = Auth::id();
        $company_id=Str::getCompany($user_id);
        $PadNumber = new ExpenseDetail();
        $lastPad = $PadNumber->where('company_id',$company_id)->orderByDesc('PadNumber')->pluck('PadNumber')->first();
        $newPad = ($lastPad + 1);
        return $newPad;
    }

    public function BaseList()
    {
        $user_id = Auth::id();
        $company_id=Str::getCompany($user_id);
        $banks = Bank::select('id','Name','Description')->get();
        $payment_type = PaymentType::select('id','Name')->get();
        $PadNumber = $this->PadNumber();
        return array('expense_category'=>ExpenseCategory::select('id','Name')->orderBy('id','desc')->get(),'employee'=>Employee::select('id','Name')->orderBy('id','desc')->get(),'supplier'=>Supplier::select('id','Name','Address','Mobile','postCode','TRNNumber')->where('company_type_id',3)->where('company_id',$company_id)->orderBy('id','desc')->get(),'banks'=>$banks,'payment_type'=>$payment_type,'pad_number'=>$PadNumber);
    }

    public function delete(Request $request, $Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = Expense::find($Id);
        $update->user_id=$userId;
        $update->save();
        $expense = Expense::withoutTrashed()->find($Id);
        if($expense->trashed())
        {
            return new ExpenseResource(Expense::onlyTrashed()->find($Id));
        }
        else
        {
            DB::table('expense_details')->where([['expense_id', $Id]])->update(['deleted_at' =>date('Y-m-d h:i:s')]);
            $expense->delete();
            return new ExpenseResource(Expense::onlyTrashed()->find($Id));
        }
    }

    public function restore($Id)
    {
        $supplier = Expense::onlyTrashed()->find($Id);
        if (!is_null($supplier))
        {
            $supplier->restore();
            return new ExpenseResource(Expense::find($Id));
        }
        return new ExpenseResource(Expense::find($Id));
    }

    public function trashed()
    {
        $supplier = Expense::onlyTrashed()->get();
        return ExpenseResource::collection($supplier);
    }

    public function ExpenseDocumentsUpload(Request $request)
    {
        try
        {
            $userId = Auth::id();
            if ($request->hasfile('document'))
            {
                foreach($request->file('document') as $document)
                {
                    $extension = $document->getClientOriginalExtension();
                    $filename=uniqid('expense_doc_'.$request->id.'_').'.'.$extension;
                    $document->storeAs('document/',$filename,'public');

                    $file_upload = new FileUpload();
                    $file_upload->Title = $filename;
                    $file_upload->RelationTable = 'expenses';
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
        if(!empty($data['expense_details']))
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
            $pdf::MultiCell(44, 5, $data['expenseNumber'], 0, 'R', 0, 2, '', '', true, 0);

            $pdf::SetXY(25,16);
            $pdf::MultiCell(147, 5, $company_mobile, 0, 'C', 0, 2, '', '', true, 0);

            $pdf::SetXY(25,20);
            $pdf::MultiCell(107, 5, $company_email, 0, 'C', 0, 2, '', '', true, 0);
            $pdf::MultiCell(71, 5, 'Date : '.date('d-m-Y', strtotime($data['expenseDate'])), 0, 'R', 0, 2, '', '', true, 0);

            $pdf::SetXY(25,24);
            $pdf::MultiCell(106, 5, 'TRN : 100330389600003', 0, 'C', 0, 2, '', '', true, 0);
            $pdf::MultiCell(72, 5, 'Due Date : '.date('d-m-Y', strtotime($data['expenseDate'])), 0, 'R', 0, 2, '', '', true, 0);

            $pdf::SetXY(28,28);
            $pdf::Image('https://watanpharma.com/images/logo-1.png', 15, 5, 40, 18, 'PNG', '', '', true, 300, '', false, false, 0, false, false, false);
            $pdf::SetXY(15,37);
            $pdf::Ln(6);

            $pdf::SetXY(25,35);
            $pdf::writeHTML("<hr>", true, false, false, false, '');

            $row=$data['expense_details'];
            $pdf::SetFont('times', '', 15);
            $html='<u><b>Expense Voucher</b></u>';
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
                    <td align="left" width="190">'.$row[$i]['PadNumber'].'</td>
                    <td align="left" width="70">'.$row[$i]['PadNumber'].'</td>
                    <td align="center" width="50">'.'N.A.'.'</td>
                    <td align="center" width="55">'.number_format($row[$i]['Total'],2,'.',',').'</td>
                    <td align="center" width="50">'.number_format($row[$i]['VAT'],2,'.',',').'</td>
                    <td align="center" width="35">'.number_format($row[$i]['rowVatAmount'],2,'.',',').'</td>
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
                    <td width="220" colspan="2" rowspan="2" style="border: 1px solid black;">'.$terms_condition.'</td>
                    <td width="175" colspan="4" rowspan="2" style="border: 1px solid black;">'.$vendor_note.'</td>
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

            $amount_in_words=$this->getUAECurrency($data['grandTotal']);
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
            $fileLocation = storage_path().'/app/public/expense_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/expense_files/'.$time.'.pdf';
            //$url=storage_path().'/purchase_order_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return $this->userResponse->Failed($purchase = (object)[],'Not Found.');
        }
    }

    function getUAECurrency(float $number)
    {
        $decimal = round($number - ($no = floor($number)), 2) * 100;
        $hundred = null;
        $digits_length = strlen($no);
        $i = 0;
        $str = array();
        $words = array(0 => '', 1 => 'one', 2 => 'two',
            3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six',
            7 => 'seven', 8 => 'eight', 9 => 'nine',
            10 => 'ten', 11 => 'eleven', 12 => 'twelve',
            13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen',
            16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen',
            19 => 'nineteen', 20 => 'twenty', 30 => 'thirty',
            40 => 'forty', 50 => 'fifty', 60 => 'sixty',
            70 => 'seventy', 80 => 'eighty', 90 => 'ninety');
        $digits = array('', 'hundred','thousand','lakh', 'crore');
        while( $i < $digits_length ) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += $divider == 10 ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
            } else $str[] = null;
        }
        $Rupees = implode('', array_reverse($str));
        $paise = ($decimal > 0) ? "." . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Fils' : '';
        return ($Rupees ? $Rupees . 'AED ' : '') . $paise;
    }
}
