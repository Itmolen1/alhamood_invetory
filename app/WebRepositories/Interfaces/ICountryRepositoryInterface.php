<?php


namespace App\WebRepositories\Interfaces;


use App\Http\Requests\CountryRequest;
use Illuminate\Http\Request;

interface ICountryRepositoryInterface
{

    public function index();

    public function create();

    public function store(CountryRequest $countryRequest);

    public function update(Request $request, $Id);

    public function getById($Id);

    public function edit($Id);

    public function delete(Request $request, $Id);

    public function  restore($Id);

    public function trashed();

}
