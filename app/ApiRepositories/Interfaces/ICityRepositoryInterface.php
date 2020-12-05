<?php


namespace App\ApiRepositories\Interfaces;


use App\Http\Requests\CityRequest;
use Illuminate\Http\Request;

interface ICityRepositoryInterface
{
    public  function all();

    public  function paginate($page_no,$page_size);

    public  function insert(Request $request);

    public  function update(CityRequest $cityRequest,$Id);

    public  function getById($Id);

    public  function delete(Request $request,$Id);

    public  function ActivateDeactivate($Id);
}
