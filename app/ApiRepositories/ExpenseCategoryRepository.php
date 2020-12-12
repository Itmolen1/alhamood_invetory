<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\IExpenseCategoryRepositoryInterface;
use App\Http\Requests\ExpenseCategoryRequest;
use App\Http\Resources\ExpenseCategory\ExpenseCategoryResource;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ExpenseCategoryRepository implements IExpenseCategoryRepositoryInterface
{
    public function all()
    {
        return ExpenseCategoryResource::collection(ExpenseCategory::all()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return ExpenseCategoryResource::Collection(ExpenseCategory::all()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(Request $request)
    {
        $userId = Auth::id();
        $expense_category = new ExpenseCategory();
        $expense_category->Name=$request->Name;
        $expense_category->Description=$request->Description;
        $expense_category->createdDate=date('Y-m-d h:i:s');
        $expense_category->isActive=1;
        $expense_category->user_id = $userId ?? 0;
        $expense_category->company_id=Str::getCompany($userId);
        $expense_category->save();
        return new ExpenseCategoryResource(ExpenseCategory::find($expense_category->id));
    }

    public function update(ExpenseCategoryRequest $expenseCategoryRequest, $Id)
    {
        $userId = Auth::id();
        $expense_category = ExpenseCategory::find($Id);
        $expenseCategoryRequest['user_id']=$userId ?? 0;
        $expense_category->update($expenseCategoryRequest->all());
        return new ExpenseCategoryResource(ExpenseCategory::find($Id));
    }

    public function getById($Id)
    {
        return new ExpenseCategoryResource(ExpenseCategory::find($Id));
    }

    public function delete(Request $request, $Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = ExpenseCategory::find($Id);
        $update->user_id=$userId;
        $update->save();
        $expense_category = ExpenseCategory::withoutTrashed()->find($Id);
        if($expense_category->trashed())
        {
            return new ExpenseCategoryResource(ExpenseCategory::onlyTrashed()->find($Id));
        }
        else
        {
            $expense_category->delete();
            return new ExpenseCategoryResource(ExpenseCategory::onlyTrashed()->find($Id));
        }
    }

    public function restore($Id)
    {
        $expense_category = ExpenseCategory::onlyTrashed()->find($Id);
        if (!is_null($expense_category))
        {
            $expense_category->restore();
            return new ExpenseCategoryResource(ExpenseCategory::find($Id));
        }
        return new ExpenseCategoryResource(ExpenseCategory::find($Id));
    }

    public function trashed()
    {
        $expense_category = ExpenseCategory::onlyTrashed()->get();
        return ExpenseCategoryResource::collection($expense_category);
    }

    public function ActivateDeactivate($Id)
    {
        $expense_category = ExpenseCategory::find($Id);
        if($expense_category->isActive==1)
        {
            $expense_category->isActive=0;
        }
        else
        {
            $expense_category->isActive=1;
        }
        $expense_category->update();
        return new ExpenseCategoryResource(ExpenseCategory::find($Id));
    }
}
