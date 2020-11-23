<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\IVehicleRepositoryInterface;
use App\Http\Controllers\Controller;
use App\MISC\ServiceResponse;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;

class VehicleController extends Controller
{
    private $userResponse;
    private $vehicleRepository;

    public function __construct(ServiceResponse $serviceResponse, IVehicleRepositoryInterface $vehicleRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->vehicleRepository=$vehicleRepository;
    }

    public function index()
    {
        try
        {
            return $this->userResponse->Success($this->vehicleRepository->all());
        }
        catch (Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function paginate($page_no,$page_size)
    {
        try
        {
            return $this->userResponse->Success($this->vehicleRepository->paginate($page_no,$page_size));
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        try
        {
            $vehicle = Vehicle::create($request->all());
            return $this->userResponse->Success($vehicle);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function show($id)
    {
        try
        {
            $vehicle = Vehicle::find($id);
            if(is_null($vehicle))
            {
                return $this->userResponse->Failed($vehicle = (object)[],'Not Found.');
            }
            return $this->userResponse->Success($vehicle);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        try
        {
            $vehicle = Vehicle::find($id);
            if(is_null($vehicle))
            {
                return $this->userResponse->Failed($vehicle = (object)[],'Not Found.');
            }
            $vehicle->update($request->all());
            $vehicle->save();
            return $this->userResponse->Success($vehicle);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function destroy(Request $request,$Id)
    {
        try
        {
            $vehicle = $this->vehicleRepository->delete($request,$Id);
            return $this->userResponse->Success($vehicle);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function restore($Id)
    {
        try {
            $restore = Vehicle::withTrashed()->where('Id', $Id)->restore();
            return $this->userResponse->Success($restore);

        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public  function  trash()
    {
        $trashed = $this->vehicleRepository->trashed();
        return $this->userResponse->Success($trashed);
    }
}
