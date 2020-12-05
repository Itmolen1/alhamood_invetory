<?php


namespace App\ApiRepositories\Interfaces;


use App\Http\Requests\ExpenseCategoryRequest;
use Illuminate\Http\Request;

interface IExpenseCategoryRepositoryInterface
{
    public  function all();

    public  function paginate($page_no,$page_size);

    public  function insert(Request $request);

    public  function update(ExpenseCategoryRequest $expenseCategoryRequest,$Id);

    public  function getById($Id);

    public  function delete(Request $request,$Id);

    public  function ActivateDeactivate($Id);
}
