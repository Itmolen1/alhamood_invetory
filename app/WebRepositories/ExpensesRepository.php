<?php


namespace App\WebRepositories;


use App\Http\Requests\ExpenseRequest;
use App\Models\AccountTransaction;
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
        // TODO: Implement index() method.
        $expenses = Expense::with('expense_details.expense_category','supplier')->get();
       // dd($expenses);
        return view('admin.expense.index',compact('expenses'));
    }

    public function create()
    {
        // TODO: Implement create() method.
        $expenseNo = $this->invoiceNumber();
        $PadNumber = $this->PadNumber();
        $suppliers = Supplier::all();
        $employees = Employee::all();
        $expense_categories = ExpenseCategory::all();
        return view('admin.expense.create',compact('suppliers','expenseNo','employees','expense_categories','PadNumber'));
    }

    public function store(ExpenseRequest $expenseRequest)
    {
        // TODO: Implement store() method.
        $AllRequestCount = collect($expenseRequest->Data)->count();
        if($AllRequestCount > 0) {
            //return Response()->json($request);
            // return Response()->json($request->Data['orders']);
            //return Response()->json($request->Data['PurchaseNumber']);
            //return Response()->json($request->Data['referenceNumber']);
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
            $expense->paidBalance = $expenseRequest->Data['paidBalance'];
            $expense->remainingBalance = $expenseRequest->Data['remainingBalance'];
            $expense->supplier_id = $expenseRequest->Data['supplier_id'];
            $expense->employee_id = $expenseRequest->Data['employee_id'];
            $expense->Description = $expenseRequest->Data['supplierNote'];
            $expense->user_id = $user_id;
            $expense->company_id = $company_id;
            $expense->save();
            $expense = $expense->id;
            //return Response()->json($purchase);
            //$user = $sale->user_id;
            // return $sale;
            foreach($expenseRequest->Data['orders'] as $detail)
            {
                //return $detail['Quantity'];
                //return Response()->json($detail['Quantity']);


                $data =  ExpenseDetail::create([
                    "Total"        => $detail['Total'],
                    "expenseDate"        => $detail['expenseDate'],
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

            ////////////////// account section ////////////////
            if ($expense)
            {
                $accountTransaction = AccountTransaction::where(
                    [
                        'company_id'=> $company_id,
                        'createdDate' => date('Y-m-d'),
                    ])->first();
                if (!is_null($accountTransaction))
                {
                    if ($expenseRequest->Data['paidBalance'] == 0 || $expenseRequest->Data['paidBalance'] == 0.00) {
                        if ($accountTransaction->createdDate != date('Y-m-d')) {
                            $totalDebit = $expenseRequest->Data['grandTotal'];
                        } else {
                            $totalDebit = $accountTransaction->Debit + $expenseRequest->Data['grandTotal'];
                        }
                        $totalCredit = $accountTransaction->Credit;
                        $difference = $accountTransaction->Differentiate - $expenseRequest->Data['grandTotal'];
                    }
                    elseif($expenseRequest->Data['paidBalance'] > 0 AND $expenseRequest->Data['paidBalance'] < $expenseRequest->Data['grandTotal'] )
                    {
                        if ($accountTransaction->createdDate != date('Y-m-d')) {
                            $totalCredit = $expenseRequest->Data['paidBalance'];
                            $totalDebit = $expenseRequest->Data['grandTotal'];
                        } else {
                            $totalCredit = $accountTransaction->Credit + $expenseRequest->Data['paidBalance'];
                            $totalDebit = $accountTransaction->Debit + $expenseRequest->Data['grandTotal'];
                        }
                        $differenceValue = $accountTransaction->Differentiate + $expenseRequest->Data['paidBalance'];
                        $difference = $differenceValue - $expenseRequest->Data['grandTotal'];
                    }
                    else{

                        if ($accountTransaction->createdDate != date('Y-m-d')) {
                            $totalCredit = $expenseRequest->Data['paidBalance'];
                        } else {
                            $totalCredit = $accountTransaction->Credit + $expenseRequest->Data['paidBalance'];
                        }
                        $totalDebit = $accountTransaction->Debit;
                        $difference = $accountTransaction->Differentiate + $expenseRequest->Data['paidBalance'];
                    }
                }
                else
                {
                    $accountTransaction = AccountTransaction::where(
                        [
                            'company_id'=> $company_id,
                        ])->get();
                    if ($expenseRequest->Data['paidBalance'] == 0 || $expenseRequest->Data['paidBalance'] == 0.00) {
                        $totalDebit = $expenseRequest->Data['grandTotal'];
                        $totalCredit = $accountTransaction->last()->Credit;
                        $difference = $accountTransaction->last()->Differentiate + $expenseRequest->Data['grandTotal'];
                    }
                    elseif($expenseRequest->Data['paidBalance'] > 0 AND $expenseRequest->Data['paidBalance'] < $expenseRequest->Data['grandTotal'] )
                    {

                        $totalCredit = $expenseRequest->Data['paidBalance'];
                        $totalDebit = $expenseRequest->Data['grandTotal'];
                        $differenceValue = $accountTransaction->last()->Differentiate - $expenseRequest->Data['paidBalance'];
                        $difference = $differenceValue + $expenseRequest->Data['grandTotal'];
                    }
                    else{
                        $totalCredit = $expenseRequest->Data['paidBalance'];
                        $totalDebit = $accountTransaction->last()->Debit;
                        $difference = $accountTransaction->last()->Differentiate - $expenseRequest->Data['paidBalance'];
                    }
                }
                $AccData =
                    [
                        'company_id' => $company_id,
                        'Credit' => $totalCredit,
                        'employee_id' => $expenseRequest->Data['employee_id'],
                        'Debit' => $totalDebit,
                        'Differentiate' => $difference,
                        'createdDate' => date('Y-m-d'),
                        'user_id' => $user_id,
                    ];
                $AccountTransactions = AccountTransaction::updateOrCreate(
                    [
                        'createdDate'   => date('Y-m-d'),
                        'company_id'   => $company_id,
                    ],
                    $AccData);
                return Response()->json($AccountTransactions);
                // return Response()->json("");
            }
            ////////////////// end of account section ////////////////
//            if ($data)
//            {
//                return Response()->json($data);
//            }
        }
    }

    public function update(Request $request, $Id)
    {
        // TODO: Implement update() method.
        $AllRequestCount = collect($request->Data)->count();
        if($AllRequestCount > 0)
        {
            $expensed = Expense::find($Id);
            $user_id = session('user_id');
            $company_id = session('company_id');

            ////////////////// account section ////////////////
            $accountTransaction = AccountTransaction::where(
                [
                    'company_id'=> $company_id,
                ])->get();
            if (!is_null($accountTransaction)) {
                $lastAccountTransection = $accountTransaction->Last();
                if ($lastAccountTransection->company_id != $expensed->company_id)
                {
                    if ($expensed->paidBalance == 0 || $expensed->paidBalance == 0.00) {
                        $OldValue1 = $expensed->company->account_transaction->Last()->Debit - $expensed->grandTotal;
                        $OldTotalDebit = $OldValue1;
                        $OldTotalCredit = $expensed->company->account_transaction->Last()->Credit;
                        $OldValue = $expensed->company->account_transaction->Last()->Differentiate + $expensed->grandTotal;
                        $OldDifference = $OldValue;
                    }
                    elseif ($expensed->paidBalance > 0 AND $expensed->paidBalance < $expensed->grandTotal)
                    {
                        $OldTotalCredit = $expensed->company->account_transaction->Last()->Credit - $expensed->paidBalance;
                        $OldTotalDebit = $expensed->company->account_transaction->Last()->Debit - $expensed->grandTotal;
                        $differenceValue = $expensed->company->account_transaction->Last()->Differentiate - $expensed->paidBalance;
                        $OldDifference = $differenceValue + $expensed->grandTotal;
                    }
                    else{
                        $OldValue1 = $expensed->company->account_transaction->Last()->Credit - $expensed->paidBalance;
                        $OldTotalCredit = $OldValue1;
                        $OldTotalDebit = $expensed->company->account_transaction->Last()->Debit;
                        $OldValue = $expensed->company->account_transaction->Last()->Differentiate - $expensed->paidBalance;
                        $OldDifference = $OldValue;
                    }
                    $OldAccData =
                        [
                            'company_id' => $expensed->company_id,
                            'employee_id' => $expensed->employee_id,
                            'Debit' => $OldTotalDebit,
                            'Credit' => $OldTotalCredit,
                            'Differentiate' => $OldDifference,
                            'createdDate' => $expensed->company->account_transaction->Last()->createdDate,
                            'user_id' =>$user_id,
                        ];
                    $AccountTransactions = AccountTransaction::updateOrCreate([
                        'id'   => $expensed->company->account_transaction->Last()->id,
                    ], $OldAccData);

                    if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00) {
                        $totalDebit = $lastAccountTransection->Debit + $request->Data['grandTotal'];
                        $totalCredit = $lastAccountTransection->Credit;
                        $difference = $lastAccountTransection->Differentiate - $request->Data['grandTotal'];
                    }
                    elseif ($request->Data['paidBalance'] > 0 AND $request->Data['paidBalance'] < $request->Data['grandTotal'])
                    {
                        $totalDebit = $lastAccountTransection->Debit - $request->Data['paidBalance'];
                        $totalCredit = $lastAccountTransection->Credit - $request->Data['grandTotal'];
                        $differenceValue = $accountTransaction->last()->Differentiate - $request->Data['paidBalance'];
                        $difference = $differenceValue + $request->Data['grandTotal'];
                    }
                    else{
                        $totalCredit = $lastAccountTransection->Credit + $request->Data['paidBalance'];
                        $totalDebit = $lastAccountTransection->Debit;
                        $difference = $lastAccountTransection->Differentiate + $request->Data['paidBalance'];
                    }
                }
                else
                {
                    if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00 || $request->Data['paidBalance'] == "") {
                        if ($lastAccountTransection->createdDate != $expensed->company->account_transaction->last()->createdDate) {
                            $totalDebit = $request->Data['grandTotal'];
                        } else {
                            $value1 = $lastAccountTransection->Debit - $expensed->grandTotal;
                            $totalDebit = $value1 + $request->Data['grandTotal'];
                        }
                        $totalCredit = $lastAccountTransection->Credit;
                        $value = $lastAccountTransection->Differentiate + $expensed->grandTotal;
                        $difference = $value - $request->Data['grandTotal'];
//                                        return Response()->json($difference);
                    }
                    elseif ($request->Data['paidBalance'] > 0 AND $request->Data['paidBalance'] < $request->Data['grandTotal'])
                    {

                        if ($lastAccountTransection->createdDate != $expensed->company->account_transaction->last()->createdDate) {
                            $totalCredit = $request->Data['paidBalance'];
                            $totalDebit = $request->Data['grandTotal'];
                        } else {
                            $value1 = $lastAccountTransection->Credit - $expensed->paidBalance;
                            $totalCredit = $value1 + $request->Data['paidBalance'];
                            $valueC = $lastAccountTransection->Debit - $expensed->grandTotal;
                            $totalDebit = $valueC + $request->Data['grandTotal'];
                        }
                        $differenceValue = $lastAccountTransection->Differentiate - $request->Data['paidBalance'];
                        $difference = $differenceValue + $request->Data['grandTotal'];
                    }
                    else{
                        if ($lastAccountTransection->createdDate != $expensed->company->account_transaction->last()->createdDate) {
                            $totalCredit = $request->Data['paidBalance'];
                        } else {
                            $value1 = $lastAccountTransection->Credit - $expensed->paidBalance;
                            $totalCredit = $value1 + $request->Data['paidBalance'];
                        }
                        $totalDebit = $lastAccountTransection->Debit;
                        $value = $lastAccountTransection->Differentiate - $expensed->paidBalance;
                        $difference = $value + $request->Data['paidBalance'];
                    }
                }

                $AccData =
                    [
                        'company_id' => $company_id,
                        'employee_id' => $request->Data['employee_id'],
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

            $expensed->update(
                [
                    'expenseNumber' => $request->Data['expenseNumber'],
                    'referenceNumber' => $request->Data['referenceNumber'],
                    'expenseDate' => $request->Data['expenseDate'],
                    'Total' => $request->Data['Total'],
                    'subTotal' => $request->Data['subTotal'],
                    'totalVat' => $request->Data['totalVat'],
                    'grandTotal' => $request->Data['grandTotal'],
                    'paidBalance' => $request->Data['paidBalance'],
                    'remainingBalance' => $request->Data['remainingBalance'],
                    'supplier_id' => $request->Data['supplier_id'],
                    'Description' => $request->Data['supplierNote'],
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
                    //"Id" => $detail['Id'],
                    "Total"        => $detail['Total'],
                    "expenseDate"        => $detail['expenseDate'],
                    "expense_category_id"        => $detail['expense_category_id'],
                    "Description"        => $detail['Description'],
                    "Vat"        => $detail['Vat'],
                    "rowVatAmount"        => $detail['rowVatAmount'],
                    "rowSubTotal"        => $detail['rowSubTotal'],
                    "company_id" => $company_id,
                    "user_id"      => $user_id,
                    "expense_id"      => $Id,
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
        // TODO: Implement edit() method.
        $update_notes = UpdateNote::with('company','user')->where(['RelationId' => $Id, 'RelationTable' => 'expenses'])->get();
        $suppliers = Supplier::all();
        $employees = Employee::all();
        $expense_categories = ExpenseCategory::all();
        $expense_details = ExpenseDetail::withTrashed()->with('expense.supplier','user')->where('expense_id', $Id)->get();
        //dd($expense_details);
        return view('admin.expense.edit',compact('expense_details','suppliers','update_notes','employees','expense_categories'));
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
        // TODO: Implement PadNumber() method.

        $PadNumber = new ExpenseDetail();
        $lastPad = $PadNumber->orderByDesc('PadNumber')->pluck('PadNumber')->first();
        $newPad = ($lastPad + 1);
        return $newPad;
    }
}
