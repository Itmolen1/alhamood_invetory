<?php


namespace App\ApiRepositories\Interfaces;


use App\Http\Requests\BankRequest;
use Illuminate\Http\Request;

interface IBankRepositoryInterface
{
    public  function all();

    public  function paginate($page_no,$page_size);

    public  function insert(Request $request);

    public  function update(BankRequest $bankRequest,$Id);

    public  function getBankById($Id);

    public  function delete(Request $request,$Id);
}
