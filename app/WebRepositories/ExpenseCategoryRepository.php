<?php


namespace App\WebRepositories;


use App\Http\Requests\ExpenseCategoryRequest;
use App\Models\ExpenseCategory;
use App\WebRepositories\Interfaces\IExpenseCategoryRepositoryInterface;
use Illuminate\Http\Request;

class ExpenseCategoryRepository implements IExpenseCategoryRepositoryInterface
{

    public function index()
    {
        // TODO: Implement index() method.
        $expense_categories = ExpenseCategory::all();
        return view('admin.expense_category.index',compact('expense_categories'));
    }

    public function create()
    {
        // TODO: Implement create() method.
    }

    public function store(ExpenseCategoryRequest $expenseCategoryRequest)
    {
        // TODO: Implement store() method.
        $user_id = session('user_id');
        $company_id = session('company_id');
        $data = [
            'Name' => $expenseCategoryRequest->Name,
            'user_id' => $user_id,
            'company_id' => $company_id,
        ];
        ExpenseCategory::create($data);
        return redirect()->route('expense_categories.index')->with('success','Record Inserted Successfully');
    }

    public function update(Request $request, $Id)
    {
        // TODO: Implement update() method.
        $data = ExpenseCategory::find($Id);
        $user_id = session('user_id');
        $company_id = session('company_id');
        $data->update([
            'Name' => $request->Name,
            'user_id' => $user_id,
            'company_id' => $company_id,
        ]);
        return redirect()->route('expense_categories.index')->with('update','Record Update Successfully');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        // TODO: Implement edit() method.
        $category = ExpenseCategory::find($Id);
        return view('admin.expense_category.edit',compact('category'));
    }

    public function delete(Request $request, $Id)
    {
        // TODO: Implement delete() method.
        $Update = ExpenseCategory::find($Id);
        $user_id = session('user_id');
        $company_id = session('company_id');
        $Update->update([
            'user_id' => $user_id,
            'company_id' => $company_id,
        ]);
        $state = ExpenseCategory::withoutTrashed()->find($Id);
        if($state->trashed())
        {
            return redirect()->route('expense_categories.index');
        }
        else
        {
            $state->delete();
            return redirect()->route('expense_categories.index')->with('delete','Record Update Successfully');
        }
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
        $state = ExpenseCategory::onlyTrashed()->find($Id);
        if (!is_null($state))
        {
            $state->restore();
            return redirect()->route('expense_categories.index')->with('restore','Record Restore Successfully');
        }
        return redirect()->route('expense_categories.index');
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
        $trashes = ExpenseCategory::with('user')->onlyTrashed()->get();
        return view('admin.expense_category.edit',compact('trashes'));
    }
}
