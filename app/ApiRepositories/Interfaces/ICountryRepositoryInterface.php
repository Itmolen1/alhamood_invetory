<?php


namespace App\ApiRepositories\Interfaces;


use App\Http\Requests\CountryRequest;
use Illuminate\Http\Request;

interface ICountryRepositoryInterface
{
    public  function all();

    public  function paginate($page_no,$page_size);

    public  function insert(Request $request);

    public  function update(CountryRequest $countryRequest,$Id);

    public  function getById($Id);

    public  function delete(Request $request,$Id);
}
