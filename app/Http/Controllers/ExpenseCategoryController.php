<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseCategoryRequest;
use App\Models\ExpenseCategory;
use App\WebRepositories\Interfaces\IExpenseCategoryRepositoryInterface;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    /**
     * @var IExpenseCategoryRepositoryInterface
     */
    private $expenseCategoryRepository;

    public function __construct(IExpenseCategoryRepositoryInterface $expenseCategoryRepository)
    {
        $this->expenseCategoryRepository = $expenseCategoryRepository;
    }

    public function index()
    {
        return $this->expenseCategoryRepository->index();
    }


    public function create()
    {
        //
    }


    public function store(ExpenseCategoryRequest $expenseCategoryRequest)
    {
        return $this->expenseCategoryRepository->store($expenseCategoryRequest);
    }

    public function show(ExpenseCategory $expenseCategory)
    {
        //
    }


    public function edit($Id)
    {
        return $this->expenseCategoryRepository->edit($Id);
    }


    public function update(Request $request, $Id)
    {
        return $this->expenseCategoryRepository->update($request, $Id);
    }


    public function destroy(Request $request, $Id)
    {
        return $this->expenseCategoryRepository->delete($request, $Id);
    }
}
