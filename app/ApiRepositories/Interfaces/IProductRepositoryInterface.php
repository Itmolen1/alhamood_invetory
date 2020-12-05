<?php


namespace App\ApiRepositories\Interfaces;


use App\Http\Requests\ProductRequest;
use Illuminate\Http\Request;

interface IProductRepositoryInterface
{
    public  function all();

    public  function paginate($page_no,$page_size);

    public  function insert(ProductRequest $productRequest);

    public  function update(Request $request,$Id);

    public  function getById($Id);

    public  function delete(Request $request,$Id);

    public  function ActivateDeactivate($Id);
}
