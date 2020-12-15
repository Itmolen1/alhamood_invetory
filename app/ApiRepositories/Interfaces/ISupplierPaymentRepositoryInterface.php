<?php


namespace App\ApiRepositories\Interfaces;


use Illuminate\Http\Request;

interface ISupplierPaymentRepositoryInterface
{
    public  function all();

    public  function paginate($page_no,$page_size);

    public  function insert(Request $request);

    public  function supplier_payments_push($Id);
}
