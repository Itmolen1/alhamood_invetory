<?php


namespace App\ApiRepositories\Interfaces;


use App\Http\Requests\LoanRequest;
use Illuminate\Http\Request;

interface ILoanRepositoryInterface
{
    public  function all();

    public  function paginate($page_no,$page_size);

    public  function insert(Request $request);

    public  function update(LoanRequest $loanRequest,$Id);

    public  function getById($Id);

    public  function delete(Request $request,$Id);
}
