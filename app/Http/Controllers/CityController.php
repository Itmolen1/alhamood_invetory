<?php

namespace App\Http\Controllers;

use App\Http\Requests\CityRequest;
use App\Models\City;
use App\WebRepositories\Interfaces\ICityRepositoryInterface;
use Illuminate\Http\Request;

class CityController extends Controller
{
    /**
     * @var ICityRepositoryInterface
     */
    private $cityRepository;

    public function __construct(ICityRepositoryInterface $cityRepository)
   {
       $this->cityRepository = $cityRepository;
   }

    public function index()
    {
        return $this->cityRepository->index();
    }


    public function create()
    {
        return $this->cityRepository->create();
    }


    public function store(CityRequest $request)
    {
        return $this->cityRepository->store($request);
    }


    public function show($Id)
    {
        //
    }


    public function edit($Id)
    {
        return $this->cityRepository->edit($Id);
    }

    public function update(Request $request, $Id)
    {

        return $this->cityRepository->update($request, $Id);
    }


    public function destroy(Request $request, $Id)
    {
        return $this->cityRepository->delete($request, $Id);
    }
}
