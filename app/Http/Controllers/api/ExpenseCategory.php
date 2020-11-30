<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\IExpenseCategoryRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExpenseCategoryRequest;
use App\MISC\ServiceResponse;
use Illuminate\Http\Request;

class ExpenseCategory extends Controller
{
    private $expenseCategoryRepository;
    private $userResponse;

    public function __construct(ServiceResponse $serviceResponse, IExpenseCategoryRepositoryInterface $expenseCategoryRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->expenseCategoryRepository=$expenseCategoryRepository;
    }

    public function index()
    {
        try
        {
            return $this->userResponse->Success($this->expenseCategoryRepository->all());
        }
        catch (Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function paginate($page_no,$page_size)
    {
        try
        {
            return $this->userResponse->Success($this->expenseCategoryRepository->paginate($page_no,$page_size));
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function store(Request $request)
    {
        return $this->expenseCategoryRepository->insert($request);
    }

    public function show($id)
    {
        try
        {
            $expense_category = \App\Models\ExpenseCategory::find($id);
            if(is_null($expense_category))
            {
                return $this->userResponse->Failed($expense_category = (object)[],'Not Found.');
            }
            return $this->userResponse->Success($expense_category);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function update(ExpenseCategoryRequest $expenseCategoryRequest, $id)
    {
        try
        {
            $expense_category = \App\Models\ExpenseCategory::find($id);
            if(is_null($expense_category))
            {
                return $this->userResponse->Failed($expense_category = (object)[],'Not Found.');
            }
            return $this->expenseCategoryRepository->update($expenseCategoryRequest,$id);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function destroy(Request $request,$Id)
    {
        try
        {
            $expense_category = \App\Models\ExpenseCategory::find($Id);
            if(is_null($expense_category))
            {
                return $this->userResponse->Failed($expense_category = (object)[],'Not Found.');
            }
            $expense_category = $this->expenseCategoryRepository->delete($request,$Id);
            return $this->userResponse->Success($expense_category);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function restore($Id)
    {
        try {
            $restore = \App\Models\ExpenseCategory::withTrashed()->where('Id', $Id)->restore();
            return $this->userResponse->Success($restore);

        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public  function  trash()
    {
        $trashed = $this->expenseCategoryRepository->trashed();
        return $this->userResponse->Success($trashed);
    }
}
