<?php


namespace App\WebRepositories\Interfaces;


use App\Http\Requests\ExpenseRequest;
use Illuminate\Http\Request;

interface IExpensesRepositoryInterface
{
    public function index();

    public function create();

    public function store(ExpenseRequest $expenseRequest);

    public function update(Request $request, $Id);

    public function getById($Id);

    public function edit($Id);

    public function delete(Request $request, $Id);

    public function  restore($Id);

    public function trashed();

    public function invoiceNumber();

    public function PadNumber();
}
