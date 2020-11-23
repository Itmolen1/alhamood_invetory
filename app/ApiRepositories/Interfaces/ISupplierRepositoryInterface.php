<?php


namespace App\ApiRepositories\Interfaces;


use App\Http\Requests\SupplierRequest;
use Illuminate\Http\Request;

interface ISupplierRepositoryInterface
{
    public  function all();

    public  function paginate($page_no,$page_size);

    public  function insert(Request $request);

    public  function update(SupplierRequest $bankRequest,$Id);

    public  function getById($Id);

    public  function delete(Request $request,$Id);
}
