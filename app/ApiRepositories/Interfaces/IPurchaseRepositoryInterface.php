<?php


namespace App\ApiRepositories\Interfaces;


use App\Http\Requests\PurchaseRequest;
use Illuminate\Http\Request;

interface IPurchaseRepositoryInterface
{
    public  function all();

    public  function paginate($page_no,$page_size);

    public  function insert(Request $request);

    public  function update(PurchaseRequest $purchaseRequest,$Id);

    public  function getById($Id);

    public  function delete(Request $request,$Id);

    public  function PurchaseDocumentsUpload(Request $request);

    public  function print($Id);

    public  function ActivateDeactivate($Id);
}
