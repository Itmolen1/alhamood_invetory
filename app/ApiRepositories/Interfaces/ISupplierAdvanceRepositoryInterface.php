<?php


namespace App\ApiRepositories\Interfaces;


use App\Http\Requests\SupplierAdvanceRequest;
use Illuminate\Http\Request;

interface ISupplierAdvanceRepositoryInterface
{
    public  function all();

    public  function paginate($page_no,$page_size);

    public  function insert(Request $request);

    public  function update(SupplierAdvanceRequest $supplierAdvanceRequest,$Id);

    public  function getById($Id);

    public  function delete(Request $request,$Id);
}
