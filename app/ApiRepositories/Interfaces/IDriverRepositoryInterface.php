<?php


namespace App\ApiRepositories\Interfaces;


use App\Http\Requests\BankRequest;
use App\Http\Requests\DriverRequest;
use Illuminate\Http\Request;

interface IDriverRepositoryInterface
{
    public  function all();

    public  function paginate($page_no,$page_size);

    public  function insert(DriverRequest $driverRequest);

    public  function update(Request $request,$Id);

    public  function getById($Id);

    public  function delete(Request $request,$Id);

    public  function ActivateDeactivate($Id);
}
