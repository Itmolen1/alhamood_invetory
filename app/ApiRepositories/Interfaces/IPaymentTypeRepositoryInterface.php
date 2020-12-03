<?php


namespace App\ApiRepositories\Interfaces;


use App\Http\Requests\PaymentTypeRequest;
use Illuminate\Http\Request;

interface IPaymentTypeRepositoryInterface
{
    public  function all();

    public  function paginate($page_no,$page_size);

    public  function insert(Request $request);

    public  function update(PaymentTypeRequest $paymentTypeRequest,$Id);

    public  function getById($Id);

    public  function delete(Request $request,$Id);

    public  function ActivateDeactivate($Id);
}
