<?php


namespace App\WebRepositories\Interfaces;


use App\Http\Requests\CityRequest;
use App\Http\Requests\RegionRequest;
use Illuminate\Http\Request;

interface IRegionRepositoryInterface
{

    public function index();

    public function create();

    public function store(RegionRequest $regionRequest);

    public function update(Request $request, $Id);

    public function getById($Id);

    public function edit($Id);

    public function delete(Request $request, $Id);

    public function  restore($Id);

    public function trashed();

    public function locationDetails($id);

}
