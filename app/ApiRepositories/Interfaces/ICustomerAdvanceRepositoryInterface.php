<?php


namespace App\ApiRepositories\Interfaces;


use App\Http\Requests\CustomerAdvanceRequest;
use Illuminate\Http\Request;

interface ICustomerAdvanceRepositoryInterface
{
    public  function all();

    public  function paginate($page_no,$page_size);

    public  function insert(Request $request);

    public  function update(CustomerAdvanceRequest $customerAdvanceRequest,$Id);

    public  function getById($Id);

    public  function delete(Request $request,$Id);

    public  function ActivateDeactivate($Id);
}
