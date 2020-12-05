<?php


namespace App\ApiRepositories\Interfaces;


use App\Http\Requests\UnitRequest;
use Illuminate\Http\Request;

interface IUnitRepositoryInterface
{
    public  function all();

    public  function paginate($page_no,$page_size);

    public  function insert(UnitRequest $unitRequest);

    public  function update(Request $request,$Id);

    public  function getById($Id);

    public  function delete(Request $request,$Id);

    public  function ActivateDeactivate($Id);
}
