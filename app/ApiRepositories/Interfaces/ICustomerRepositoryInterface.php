<?php


namespace App\ApiRepositories\Interfaces;


use App\Http\Requests\CustomerRequest;
use Illuminate\Http\Request;

interface ICustomerRepositoryInterface
{
    public  function all();

    public  function paginate($page_no,$page_size);

    public  function insert(Request $request);

    public  function update(CustomerRequest $customerRequest,$Id);

    public  function getById($Id);

    public  function delete(Request $request,$Id);

    public  function ActivateDeactivate($Id);
}
