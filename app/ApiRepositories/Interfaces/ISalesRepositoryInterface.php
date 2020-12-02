<?php


namespace App\ApiRepositories\Interfaces;


use App\Http\Requests\SaleRequest;
use Illuminate\Http\Request;

interface ISalesRepositoryInterface
{
    public  function all();

    public  function paginate($page_no,$page_size);

    public  function insert(Request $request);

    public  function update(SaleRequest $saleRequest,$Id);

    public  function getById($Id);

    public  function delete(Request $request,$Id);

    public  function SalesDocumentsUpload(Request $request);

    public  function print($Id);

    public  function ActivateDeactivate($Id);
}
