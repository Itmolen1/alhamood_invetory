<?php


namespace App\WebRepositories\Interfaces;


use App\Http\Requests\CountryRequest;
use App\Http\Requests\UnitRequest;
use Illuminate\Http\Request;

interface IUnitRepositoryInterface
{

    public function index();

    public function create();

    public function store(UnitRequest $unitRequest);

    public function update(Request $request, $Id);

    public function getById($Id);

    public function edit($Id);

    public function delete(Request $request, $Id);

    public function  restore($Id);

    public function trashed();

}
