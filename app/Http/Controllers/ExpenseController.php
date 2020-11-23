<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseRequest;
use App\Models\Expense;
use App\WebRepositories\Interfaces\IExpenseRepositoryInterface;
use App\WebRepositories\Interfaces\IExpensesRepositoryInterface;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    private $expensesRepository;
    public function __construct(IExpensesRepositoryInterface $expensesRepository)
    {
       $this->expensesRepository = $expensesRepository;
    }

    public function index()
    {
       return $this->expensesRepository->index();
    }

    public function create()
    {
        return $this->expensesRepository->create();
    }


    public function store(ExpenseRequest $expenseRequest)
    {
        return $this->expensesRepository->store($expenseRequest);
    }

    public function show($Id)
    {
        //
    }


    public function edit($Id)
    {
        return $this->expensesRepository->edit($Id);
    }


    public function expenseUpdate(Request $request, $Id)
    {
        return $this->expensesRepository->update($request, $Id);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Expense  $expense
     * @return \Illuminate\Http\Response
     */
    public function destroy(Expense $expense)
    {
        //
    }
}
