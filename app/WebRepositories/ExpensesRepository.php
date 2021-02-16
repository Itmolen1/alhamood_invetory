<?php


namespace App\WebRepositories;


use App\Http\Requests\ExpenseRequest;
use App\Models\AccountTransaction;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\CashTransaction;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseDetail;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\UpdateNote;
use App\WebRepositories\Interfaces\IExpensesRepositoryInterface;
use Illuminate\Http\Request;

class ExpensesRepository implements IExpensesRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(Expense::with('expense_details.expense_category','supplier')->where('company_id',session('company_id'))->latest()->get())
                ->addColumn('action', function ($data) {
                    $button = '<form action="'.route('expenses.destroy', $data->id).'" method="POST">';
                    $button .= @csrf_field();
                    $button .= @method_field('DELETE');
                    $button .= '<a href="'.route('expenses.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" class=" btn btn-danger btn-sm" onclick="ConfirmDelete()"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                    $button .= '</form>';
                    return $button;
                })
                ->addColumn('expenseCategory', function($data) {
                    return $data->expense_details[0]->expense_category->Name ?? "No Data";
                })
                ->addColumn('supplier', function($data) {
                    return $data->supplier->Name ?? "No Data";
                })
                ->rawColumns([
                    'action',
                    'referenceNumber',
                    'subTotal',
                    'totalVat',
                    'grandTotal',
                    'expenseDate',
                    'supplier',
                ])
                ->make(true);
        }
        //$expenses = Expense::with('expense_details.expense_category','supplier')->where('company_id',session('company_id'))->get();
        return view('admin.expense.index');
    }

    public function create()
    {
        $expenseNo = $this->invoiceNumber();
        $PadNumber = $this->PadNumber();
        $suppliers = Supplier::all()->where('company_type_id','=',3);
        $employees = Employee::all();
        $expense_categories = ExpenseCategory::all();
        $banks = Bank::all();
        return view('admin.expense.create',compact('suppliers','expenseNo','employees','expense_categories','PadNumber','banks'));
    }

    public function store(ExpenseRequest $expenseRequest)
    {
        $AllRequestCount = collect($expenseRequest->Data)->count();
        if($AllRequestCount > 0) {

            $user_id = session('user_id');
            $company_id = session('company_id');

            $expense = new Expense();
            $expense->expenseNumber = $expenseRequest->Data['expenseNumber'];
            $expense->referenceNumber = $expenseRequest->Data['referenceNumber'];
            $expense->expenseDate = $expenseRequest->Data['expenseDate'];
            $expense->Total = $expenseRequest->Data['Total'];
            $expense->subTotal = $expenseRequest->Data['subTotal'];
            $expense->totalVat = $expenseRequest->Data['totalVat'];
            $expense->grandTotal = $expenseRequest->Data['grandTotal'];
            $expense->paidBalance = $expenseRequest->Data['grandTotal'];
            $expense->remainingBalance = 0.00;

            $expense->payment_type = $expenseRequest->Data['payment_type'];
            if($expenseRequest->Data['payment_type']!='cash')
            {
                $expense->bank_id = $expenseRequest->Data['bank_id'];
                $expense->accountNumber = $expenseRequest->Data['accountNumber'];
                $expense->transferDate = $expenseRequest->Data['transferDate'];
                $expense->ChequeNumber = $expenseRequest->Data['ChequeNumber'];
            }
            $expense->supplier_id = $expenseRequest->Data['supplier_id'];
            $expense->employee_id = $expenseRequest->Data['employee_id'];
            $expense->user_id = $user_id;
            $expense->company_id = $company_id;
            $expense->save();
            $expense = $expense->id;

            foreach($expenseRequest->Data['orders'] as $detail)
            {
                $data =  ExpenseDetail::create([
                    "Total"        => $detail['Total'],
                    "expenseDate"        => $expenseRequest->Data['expenseDate'],
                    "expense_category_id"        => $detail['expense_category_id'],
                    "Description"        => $detail['description'],
                    "Vat"        => $detail['Vat'],
                    "rowVatAmount"        => $detail['rowVatAmount'],
                    "rowSubTotal"        => $detail['rowSubTotal'],
                    "company_id" => $company_id,
                    "user_id"      => $user_id,
                    "expense_id"      => $expense,
                    "PadNumber" => $detail['padNumber'],
                ]);
            }

            $accountDescriptionString='';
            if($expenseRequest->Data['payment_type']=='cash')
            {
                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                $difference = $cashTransaction->last()->Differentiate;
                $cash_transaction = new CashTransaction();
                $cash_transaction->Reference=$expense;
                $cash_transaction->createdDate=$expenseRequest->Data['expenseDate'];
                $cash_transaction->Type='expenses';
                $cash_transaction->Details='CashExpense|'.$expense;
                $cash_transaction->Credit=$expenseRequest->Data['grandTotal'];
                $cash_transaction->Debit=0.00;
                $cash_transaction->Differentiate=$difference-$expenseRequest->Data['grandTotal'];
                $cash_transaction->user_id = $user_id;
                $cash_transaction->company_id = $company_id;
                $cash_transaction->PadNumber = $expenseRequest->Data['referenceNumber'];
                $cash_transaction->save();

                $accountDescriptionString='CashExpense|';
            }
            else
            {
                if($expenseRequest->Data['payment_type']=='bank')
                {
                    $bankTransaction = BankTransaction::where(['bank_id'=> $expenseRequest->Data['bank_id']])->get();
                    $difference = $bankTransaction->last()->Differentiate;
                    $bank_transaction = new BankTransaction();
                    $bank_transaction->Reference=$expense;
                    $bank_transaction->createdDate=$expenseRequest->Data['transferDate'] ?? date('Y-m-d h:i:s');
                    $bank_transaction->Type='expenses';
                    $bank_transaction->Details='BankTransferExpense|'.$expense;
                    $bank_transaction->Credit=$expenseRequest->Data['grandTotal'];
                    $bank_transaction->Debit=0.00;
                    $bank_transaction->Differentiate=$difference-$expenseRequest->Data['grandTotal'];
                    $bank_transaction->user_id = $user_id;
                    $bank_transaction->company_id = $company_id;
                    $bank_transaction->bank_id = $expenseRequest->Data['bank_id'];
                    $bank_transaction->updateDescription = $expenseRequest->Data['ChequeNumber'];
                    $bank_transaction->save();

                    $accountDescriptionString='BankTransferExpense|';
                }
                elseif($expenseRequest->Data['payment_type']=='cheque')
                {
                    $bankTransaction = BankTransaction::where(['bank_id'=> $expenseRequest->Data['bank_id']])->get();
                    $difference = $bankTransaction->last()->Differentiate;
                    $bank_transaction = new BankTransaction();
                    $bank_transaction->Reference=$expense;
                    $bank_transaction->createdDate=$expenseRequest->Data['transferDate'] ?? date('Y-m-d h:i:s');
                    $bank_transaction->Type='expenses';
                    $bank_transaction->Details='ChequeExpense|'.$expense;
                    $bank_transaction->Credit=$expenseRequest->Data['grandTotal'];
                    $bank_transaction->Debit=0.00;
                    $bank_transaction->Differentiate=$difference-$expenseRequest->Data['grandTotal'];
                    $bank_transaction->user_id = $user_id;
                    $bank_transaction->company_id = $company_id;
                    $bank_transaction->bank_id = $expenseRequest->Data['bank_id'];
                    $bank_transaction->updateDescription = $expenseRequest->Data['ChequeNumber'];
                    $bank_transaction->save();

                    $accountDescriptionString='ChequeExpense|';
                }
            }

            ////////////////// start account section gautam ////////////////
            if ($expense)
            {
                $accountTransaction = AccountTransaction::where(['supplier_id'=> $expenseRequest->Data['supplier_id'],])->get();

                // fully paid with cash or bank

                $totalCredit = $expenseRequest->Data['grandTotal'];
                $difference = $accountTransaction->last()->Differentiate + $expenseRequest->Data['grandTotal'];

                //make credit entry for the expense
                $AccountTransactions=AccountTransaction::Create([
                    'supplier_id' => $expenseRequest->Data['supplier_id'],
                    'Credit' => $totalCredit,
                    'Debit' => 0.00,
                    'Differentiate' => $difference,
                    'createdDate' => $expenseRequest->Data['expenseDate'],
                    'user_id' => $user_id,
                    'company_id' => $company_id,
                    'Description'=>'Expense|'.$expense,
                ]);

                //make debit entry for the whatever cash or bank account is credited
                $difference=$difference-$expenseRequest->Data['grandTotal'];
                $AccountTransactions=AccountTransaction::Create([
                    'supplier_id' => $expenseRequest->Data['supplier_id'],
                    'Credit' => 0.00,
                    'Debit' => $expenseRequest->Data['grandTotal'],
                    'Differentiate' => $difference,
                    'createdDate' => $expenseRequest->Data['expenseDate'],
                    'user_id' => $user_id,
                    'company_id' => $company_id,
                    'referenceNumber'=>$expenseRequest->Data['ChequeNumber'] ?? '',
                    'Description'=>$accountDescriptionString.$expense,
                    'updateDescription'=>$expenseRequest->Data['ChequeNumber'] ?? '',
                ]);
                return Response()->json($AccountTransactions);
            }
            ////////////////// end account section gautam ////////////////
        }
        //return false;
    }

    public function update(Request $request, $Id)
    {
        $AllRequestCount = collect($request->Data)->count();
        if($AllRequestCount > 0)
        {
            $expensed = Expense::find($Id);
            $user_id = session('user_id');
            $company_id = session('company_id');

            ////////////////// account section gautam ////////////////
            $accountTransaction = AccountTransaction::where(['supplier_id'=> $expensed->supplier_id,])->get();
            if (!is_null($accountTransaction))
            {
                // identify only and only payment method is changing
                if($expensed->payment_type!=$request->Data['payment_type'] && $expensed->supplier_id==$request->Data['supplier_id'] && $expensed->grandTotal==$request->Data['grandTotal'])
                {
                    // start reverse entry for wrong payment method
                    if($expensed->payment_type=='cash')
                    {
                        $description_string='CashExpense|'.$Id;

                        $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                        $difference = $cashTransaction->last()->Differentiate;
                        $cash_transaction = new CashTransaction();
                        $cash_transaction->Reference=$Id;
                        $cash_transaction->createdDate=$request->Data['expenseDate'];
                        $cash_transaction->Type='expenses';
                        $cash_transaction->Details='CashExpenseReversal|'.$Id;
                        $cash_transaction->Credit=0.00;
                        $cash_transaction->Debit=$request->Data['grandTotal'];
                        $cash_transaction->Differentiate=$difference+$request->Data['grandTotal'];
                        $cash_transaction->user_id = $user_id;
                        $cash_transaction->company_id = $company_id;
                        $cash_transaction->save();
                    }
                    elseif($expensed->payment_type=='bank')
                    {
                        $description_string='BankTransferExpense|'.$Id;

                        $bankTransaction = BankTransaction::where(['bank_id'=> $expensed->bank_id])->get();
                        $difference = $bankTransaction->last()->Differentiate;
                        $bank_transaction = new BankTransaction();
                        $bank_transaction->Reference=$Id;
                        $bank_transaction->createdDate=$request->Data['transferDate'] ?? date('Y-m-d h:i:s');
                        $bank_transaction->Type='expenses';
                        $bank_transaction->Details='BankTransferExpenseReversal|'.$Id;
                        $bank_transaction->Credit=0.00;
                        $bank_transaction->Debit=$request->Data['grandTotal'];
                        $bank_transaction->Differentiate=$difference+$request->Data['grandTotal'];
                        $bank_transaction->user_id = $user_id;
                        $bank_transaction->company_id = $company_id;
                        $bank_transaction->bank_id = $expensed->bank_id;
                        $bank_transaction->updateDescription = '';
                        $bank_transaction->save();
                    }
                    elseif($expensed->payment_type=='cheque')
                    {
                        $description_string='ChequeExpense|'.$Id;

                        $bankTransaction = BankTransaction::where(['bank_id'=> $expensed->bank_id])->get();
                        $difference = $bankTransaction->last()->Differentiate;
                        $bank_transaction = new BankTransaction();
                        $bank_transaction->Reference=$Id;
                        $bank_transaction->createdDate=$request->Data['transferDate'] ?? date('Y-m-d h:i:s');
                        $bank_transaction->Type='expenses';
                        $bank_transaction->Details='ChequeExpenseReversal|'.$Id;
                        $bank_transaction->Credit=0.00;
                        $bank_transaction->Debit=$request->Data['grandTotal'];
                        $bank_transaction->Differentiate=$difference+$request->Data['grandTotal'];
                        $bank_transaction->user_id = $user_id;
                        $bank_transaction->company_id = $company_id;
                        $bank_transaction->bank_id = $expensed->bank_id;
                        $bank_transaction->updateDescription = '';
                        $bank_transaction->save();
                    }
                    $previous_entry = AccountTransaction::get()->where('company_id','=',$company_id)->where('supplier_id','=',$expensed->supplier_id)->where('Description','like',$description_string)->last();
                    if($previous_entry)
                    {
                        $new_description_string='';
                        $new_update_description='';
                        if($request->Data['payment_type']=='cash')
                        {
                            $new_description_string='CashExpense|'.$Id;
                            $new_update_description=$request->Data['referenceNumber'];

                            $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                            $difference = $cashTransaction->last()->Differentiate;
                            $cash_transaction = new CashTransaction();
                            $cash_transaction->Reference=$Id;
                            $cash_transaction->createdDate=$request->Data['expenseDate'];
                            $cash_transaction->Type='expenses';
                            $cash_transaction->Details='CashExpense|'.$Id;
                            $cash_transaction->Credit=$request->Data['grandTotal'];
                            $cash_transaction->Debit=0.00;
                            $cash_transaction->Differentiate=$difference-$request->Data['grandTotal'];
                            $cash_transaction->user_id = $user_id;
                            $cash_transaction->company_id = $company_id;
                            $cash_transaction->save();
                        }
                        elseif($request->Data['payment_type']=='bank')
                        {
                            $new_description_string='BankTransferExpense|'.$Id;
                            $new_update_description=$request->Data['ChequeNumber'];

                            $bankTransaction = BankTransaction::where(['bank_id'=> $request->Data['bank_id']])->get();
                            $difference = $bankTransaction->last()->Differentiate;
                            $bank_transaction = new BankTransaction();
                            $bank_transaction->Reference=$Id;
                            $bank_transaction->createdDate=$request->Data['transferDate'] ?? date('Y-m-d h:i:s');
                            $bank_transaction->Type='expenses';
                            $bank_transaction->Details='BankTransferExpense|'.$Id;
                            $bank_transaction->Credit=$request->Data['grandTotal'];
                            $bank_transaction->Debit=0.00;
                            $bank_transaction->Differentiate=$difference-$request->Data['grandTotal'];
                            $bank_transaction->user_id = $user_id;
                            $bank_transaction->company_id = $company_id;
                            $bank_transaction->bank_id = $request->Data['bank_id'];
                            $bank_transaction->updateDescription = $request->Data['ChequeNumber'];
                            $bank_transaction->save();
                        }
                        elseif($request->Data['payment_type']=='cheque')
                        {
                            $new_description_string='ChequeExpense|'.$Id;
                            $new_update_description=$request->Data['ChequeNumber'];

                            $bankTransaction = BankTransaction::where(['bank_id'=> $request->Data['bank_id']])->get();
                            $difference = $bankTransaction->last()->Differentiate;
                            $bank_transaction = new BankTransaction();
                            $bank_transaction->Reference=$Id;
                            $bank_transaction->createdDate=$request->Data['transferDate'] ?? date('Y-m-d h:i:s');
                            $bank_transaction->Type='expenses';
                            $bank_transaction->Details='ChequeExpense|'.$Id;
                            $bank_transaction->Credit=$request->Data['grandTotal'];
                            $bank_transaction->Debit=0.00;
                            $bank_transaction->Differentiate=$difference-$request->Data['grandTotal'];
                            $bank_transaction->user_id = $user_id;
                            $bank_transaction->company_id = $company_id;
                            $bank_transaction->bank_id = $request->Data['bank_id'];
                            $bank_transaction->updateDescription = $request->Data['ChequeNumber'];
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
                elseif($expensed->payment_type!=$request->Data['payment_type'] || $expensed->supplier_id!=$request->Data['supplier_id'] || $expensed->grandTotal!=$request->Data['grandTotal'])
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
                            'createdDate' => $request->Data['expenseDate'],
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'Expense|'.$Id,
                            'updateDescription'=>'hide',
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
                        $cash_transaction->PadNumber = $request->Data['referenceNumber'];
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
                                'createdDate' => $request->Data['expenseDate'],
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'CashExpense|'.$Id,
                                'updateDescription'=>'hide',
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // also hide previous entry start
                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                        // also hide previous entry end
                    }
                    elseif($expensed->payment_type=='bank')
                    {
                        $description_string='BankTransferExpense|'.$Id;

                        $bankTransaction = BankTransaction::where(['bank_id'=> $request->Data['bank_id']])->get();
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
                                'createdDate' => $request->Data['expenseDate'],
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'BankTransferExpense|'.$Id,
                                'updateDescription'=>'hide',
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // also hide previous entry start
                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                        // also hide previous entry end
                    }
                    elseif($expensed->payment_type=='cheque')
                    {
                        $description_string='ChequeExpense|'.$Id;

                        $bankTransaction = BankTransaction::where(['bank_id'=> $request->Data['bank_id']])->get();
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
                                'createdDate' => $request->Data['expenseDate'],
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'ChequeExpense|'.$Id,
                                'updateDescription'=>'hide',
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // also hide previous entry start
                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                        // also hide previous entry end
                    }

                    // new entry start
                    $accountTransaction = AccountTransaction::where(['supplier_id'=> $request->Data['supplier_id'],])->get();
                    $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                    $AccData =
                        [
                            'supplier_id' => $request->Data['supplier_id'],
                            'Credit' => $request->Data['grandTotal'],
                            'Debit' => 0.00,
                            'Differentiate' => $difference,
                            'createdDate' => $request->Data['expenseDate'],
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'Expense|'.$Id,
                        ];
                    $AccountTransactions = AccountTransaction::Create($AccData);

                    $new_description_string='';
                    $new_update_description='';
                    if($request->Data['payment_type']=='cash')
                    {
                        $new_description_string='CashExpense|'.$Id;
                        $new_update_description=$request->Data['referenceNumber'];

                        $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                        $difference = $cashTransaction->last()->Differentiate;
                        $cash_transaction = new CashTransaction();
                        $cash_transaction->Reference=$Id;
                        $cash_transaction->createdDate=$request->Data['expenseDate'];
                        $cash_transaction->Type='expenses';
                        $cash_transaction->Details='CashExpense|'.$Id;
                        $cash_transaction->Credit=$request->Data['grandTotal'];
                        $cash_transaction->Debit=0.00;
                        $cash_transaction->Differentiate=$difference-$request->Data['grandTotal'];
                        $cash_transaction->user_id = $user_id;
                        $cash_transaction->company_id = $company_id;
                        $cash_transaction->save();
                    }
                    elseif($request->Data['payment_type']=='bank')
                    {
                        $new_description_string='BankTransferExpense|'.$Id;
                        $new_update_description=$request->Data['ChequeNumber'];

                        $bankTransaction = BankTransaction::where(['bank_id'=> $request->Data['bank_id']])->get();
                        $difference = $bankTransaction->last()->Differentiate;
                        $bank_transaction = new BankTransaction();
                        $bank_transaction->Reference=$Id;
                        $bank_transaction->createdDate=$request->Data['transferDate'] ?? date('Y-m-d h:i:s');
                        $bank_transaction->Type='expenses';
                        $bank_transaction->Details='BankTransferExpense|'.$Id;
                        $bank_transaction->Credit=$request->Data['grandTotal'];
                        $bank_transaction->Debit=0.00;
                        $bank_transaction->Differentiate=$difference-$request->Data['grandTotal'];
                        $bank_transaction->user_id = $user_id;
                        $bank_transaction->company_id = $company_id;
                        $bank_transaction->bank_id = $request->Data['bank_id'];
                        $bank_transaction->updateDescription = $request->Data['ChequeNumber'];
                        $bank_transaction->save();
                    }
                    elseif($request->Data['payment_type']=='cheque')
                    {
                        $new_description_string='ChequeExpense|'.$Id;
                        $new_update_description=$request->Data['ChequeNumber'];

                        $bankTransaction = BankTransaction::where(['bank_id'=> $request->Data['bank_id']])->get();
                        $difference = $bankTransaction->last()->Differentiate;
                        $bank_transaction = new BankTransaction();
                        $bank_transaction->Reference=$Id;
                        $bank_transaction->createdDate=$request->Data['transferDate'] ?? date('Y-m-d h:i:s');
                        $bank_transaction->Type='expenses';
                        $bank_transaction->Details='ChequeExpense|'.$Id;
                        $bank_transaction->Credit=$request->Data['grandTotal'];
                        $bank_transaction->Debit=0.00;
                        $bank_transaction->Differentiate=$difference-$request->Data['grandTotal'];
                        $bank_transaction->user_id = $user_id;
                        $bank_transaction->company_id = $company_id;
                        $bank_transaction->bank_id = $request->Data['bank_id'];
                        $bank_transaction->updateDescription = $request->Data['ChequeNumber'];
                        $bank_transaction->save();
                    }
                    //make debit entry for the whatever cash or bank account is credited
                    $accountTransaction = AccountTransaction::where(['supplier_id'=> $request->Data['supplier_id'],])->get();
                    $difference = $accountTransaction->last()->Differentiate - $request->Data['grandTotal'];
                    $AccountTransactions=AccountTransaction::Create([
                        'supplier_id' => $request->Data['supplier_id'],
                        'Credit' => 0.00,
                        'Debit' => $request->Data['grandTotal'],
                        'Differentiate' => $difference,
                        'createdDate' => $request->Data['expenseDate'],
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
            $transferDate=$request->Data['expenseDate'];
            $ChequeNumber=NULL;
            if($request->Data['payment_type']!='cash')
            {
                $bank_id=$request->Data['bank_id'];
                $accountNumber=$request->Data['accountNumber'];
                $transferDate=$request->Data['transferDate'];
                $ChequeNumber=$request->Data['ChequeNumber'];
            }
            $expensed->update(
                [
                    'expenseNumber' => $request->Data['expenseNumber'],
                    'referenceNumber' => $request->Data['referenceNumber'],
                    'expenseDate' => $request->Data['expenseDate'],
                    'Total' => $request->Data['Total'],
                    'subTotal' => $request->Data['subTotal'],
                    'totalVat' => $request->Data['totalVat'],
                    'grandTotal' => $request->Data['grandTotal'],
                    'paidBalance' => $request->Data['grandTotal'],
                    'remainingBalance' => 0.00,
                    'payment_type' => $request->Data['payment_type'],
                    'bank_id' => $bank_id,
                    'accountNumber' => $accountNumber,
                    'transferDate' => $transferDate,
                    'ChequeNumber' => $ChequeNumber,
                    'supplier_id' => $request->Data['supplier_id'],
                    'employee_id' => $request->Data['employee_id'],
                    'user_id' => $user_id,
                    'company_id' => $company_id,
                ]);

            $update_note = new UpdateNote();
            $update_note->RelationTable = 'expenses';
            $update_note->RelationId = $Id;
            $update_note->Description = $request->Data['UpdateDescription'];
            $update_note->user_id = $user_id;
            $update_note->company_id = $company_id;
            $update_note->save();

            $d = ExpenseDetail::where('expense_id', array($Id))->delete();
            $slct = ExpenseDetail::where('expense_id', $Id)->get();
            foreach ($request->Data['orders'] as $detail)
            {
                $expenseDetails = ExpenseDetail::create([
                    "Total" => $detail['Total'],
                    "expenseDate" => $request->Data['expenseDate'],
                    "expense_category_id" => $detail['expense_category_id'],
                    "Vat" => $detail['Vat'],
                    "rowVatAmount" => $detail['rowVatAmount'],
                    "rowSubTotal" => $detail['rowSubTotal'],
                    "company_id" => $company_id,
                    "user_id" => $user_id,
                    "expense_id" => $Id,
                    "PadNumber" => $detail['padNumber'],
                ]);
            }
            $ss = ExpenseDetail::where('expense_id', array($expenseDetails['expense_id']))->get();
            return Response()->json($ss);
        }
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        $update_notes = UpdateNote::with('company','user')->where(['RelationId' => $Id, 'RelationTable' => 'expenses'])->get();
        $suppliers = Supplier::all();
        $employees = Employee::all();
        $expense_categories = ExpenseCategory::all();
        $expense_details = ExpenseDetail::withTrashed()->with('expense.supplier','user')->where('expense_id', $Id)->get();
        $banks = Bank::all();
        return view('admin.expense.edit',compact('expense_details','suppliers','update_notes','employees','expense_categories','banks'));
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
        $invoice = new Expense();
        $lastInvoiceID = $invoice->orderByDesc('id')->pluck('id')->first();
        $newInvoiceID = 'EXP-00'.($lastInvoiceID + 1);
        return $newInvoiceID;
    }

    public function PadNumber()
    {
//        $PadNumber = new ExpenseDetail();
//        $lastPad = $PadNumber->orderByDesc('PadNumber')->pluck('PadNumber')->first();
//        $newPad = ($lastPad + 1);
//        return $newPad;

        $PadNumber = new ExpenseDetail();
        $lastPad = $PadNumber->where('company_id',session('company_id'))->orderByDesc('PadNumber')->pluck('PadNumber')->first();
        $newPad = ($lastPad + 1);
        return $newPad;
    }
}
