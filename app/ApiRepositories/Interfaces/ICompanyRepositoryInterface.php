<?php


namespace App\ApiRepositories\Interfaces;


use App\Http\Requests\CompanyRequest;
use Illuminate\Http\Request;

interface ICompanyRepositoryInterface
{
    public  function all();

    public  function paginate($page_no,$page_size);

    public  function insert(CompanyRequest $companyRequest);

    public  function update(Request $request,$Id);

    public  function getById($Id);

    public  function delete(Request $request,$Id);

    public  function ActivateDeactivate($Id);
}
