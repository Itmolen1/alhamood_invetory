<?php


namespace App\ApiRepositories\Interfaces;


use App\Http\Requests\EmployeeRquest;
use Illuminate\Http\Request;

interface IEmployeeRepositoryInterface
{
    public  function all();

    public  function paginate($page_no,$page_size);

    public  function insert(Request $request);

    public  function update(EmployeeRquest $employeeRquest,$Id);

    public  function getById($Id);

    public  function delete(Request $request,$Id);
}
