<?php


namespace App\ApiRepositories\Interfaces;


use App\Http\Requests\StateRequest;
use Illuminate\Http\Request;

interface IStateRepositoryInterface
{
    public  function all();

    public  function paginate($page_no,$page_size);

    public  function insert(Request $request);

    public  function update(StateRequest $stateRequest,$Id);

    public  function getById($Id);

    public  function delete(Request $request,$Id);

    public  function ActivateDeactivate($Id);
}
