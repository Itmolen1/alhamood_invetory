<?php


namespace App\ApiRepositories\Interfaces;


use App\Http\Requests\EmployeeRquest;
use Illuminate\Http\Request;

interface IEmployeeRepositoryInterface
{
    public  function all();

    public  function paginate($page_no,$page_size);

    public  function insert(EmployeeRquest $employeeRquest);

    public  function update(Request $request,$Id);

    public  function getById($Id);

    public  function delete(Request $request,$Id);

    public  function ActivateDeactivate($Id);
}
