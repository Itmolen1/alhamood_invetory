<?php

namespace App\Http\Controllers;

use App\Http\Requests\VehicleRequest;
use App\Models\Vehicle;
use App\WebRepositories\Interfaces\IVehicleRepositoryInterface;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * @var IVehicleRepositoryInterface
     */
    private $vehicleRepository;

    public function __construct(IVehicleRepositoryInterface $vehicleRepository)
    {
        $this->vehicleRepository = $vehicleRepository;
    }

    public function index()
    {
        return $this->vehicleRepository->index();
    }

    public function create()
    {
        return $this->vehicleRepository->create();
    }


    public function store(VehicleRequest $vehicleRequest)
    {
        return $this->vehicleRepository->store($vehicleRequest);
    }


    public function show(Vehicle $vehicle)
    {
        //
    }


    public function edit($Id)
    {
        return $this->vehicleRepository->edit($Id);
    }

    public function update(Request $request, $Id)
    {
        return $this->vehicleRepository->update($request, $Id);
    }


    public function destroy(Request $request, $Id)
    {
        return $this->vehicleRepository->delete($request, $Id);
    }
}
