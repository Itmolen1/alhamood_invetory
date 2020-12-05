<?php


namespace App\ApiRepositories\Interfaces;


use App\Http\Requests\CompanyTypeRequest;
use Illuminate\Http\Request;

interface ICompanyTypeRepositoryInterface
{
    public  function all();

    public  function paginate($page_no,$page_size);

    public  function insert(CompanyTypeRequest $companyTypeRequest);

    public  function update(Request $request,$Id);

    public  function getById($Id);

    public  function delete(Request $request,$Id);

    public  function ActivateDeactivate($Id);
}
