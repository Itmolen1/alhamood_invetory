<?php


namespace App\WebRepositories;


use App\Http\Requests\ExpenseRequest;
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
        $suppliers = Supplier::all();
        $employees = Employee::all();
        $expense_categories = ExpenseCategory::all();
        return view('admin.expense.create',compact('suppliers','expenseNo','employees','expense_categories'));
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

            $expensed = Expense::find($Id);
            $user_id = session('user_id');
            $company_id = session('company_id');
            //return $request;
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
        $update_notes = UpdateNote::with('company','user')->where('RelationId',$Id)->get();
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
}
