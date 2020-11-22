<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegionRequest;
use App\Models\City;
use App\Models\Region;
use App\Models\State;
use App\WebRepositories\Interfaces\IRegionRepositoryInterface;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    /**
     * @var IRegionRepositoryInterface
     */
    private $regionRepository;

    public function __construct(IRegionRepositoryInterface $regionRepository)
    {
        $this->regionRepository = $regionRepository;
    }

    public function index()
    {
        return $this->regionRepository->index();
    }

    public function create()
    {
        return $this->regionRepository->create();
    }


    public function store(RegionRequest $regionRequest)
    {
        return $this->regionRepository->store($regionRequest);
    }


    public function show($Id)
    {
        //
    }

    public function edit($Id)
    {
        return $this->regionRepository->edit($Id);
    }


    public function update(Request $request,  $Id)
    {
        return $this->regionRepository->update($request, $Id);
    }


    public function destroy(Request $request, $Id)
    {
        return $this->regionRepository->delete($request, $Id);
    }

    public function locationDetails($id)
    {
       return $this->regionRepository->locationDetails($id);
    }
}
