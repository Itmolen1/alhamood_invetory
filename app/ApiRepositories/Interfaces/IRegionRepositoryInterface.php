<?php


namespace App\ApiRepositories\Interfaces;


use App\Http\Requests\RegionRequest;
use Illuminate\Http\Request;

interface IRegionRepositoryInterface
{
    public  function all();

    public  function paginate($page_no,$page_size);

    public  function insert(RegionRequest $regionRequest);

    public  function update(Request $request,$Id);

    public  function getById($Id);

    public  function delete(Request $request,$Id);

    public  function ActivateDeactivate($Id);
}
