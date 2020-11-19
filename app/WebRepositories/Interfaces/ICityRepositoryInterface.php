<?php


namespace App\WebRepositories\Interfaces;


use App\Http\Requests\CityRequest;
use Illuminate\Http\Request;

interface ICityRepositoryInterface
{

    public function index();

    public function create();

    public function store(CityRequest $cityRequest);

    public function update(Request $request, $Id);

    public function getById($Id);

    public function edit($Id);

    public function delete(Request $request, $Id);

    public function  restore($Id);

    public function trashed();
}
