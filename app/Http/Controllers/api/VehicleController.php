<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\IVehicleRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\VehicleRequest;
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

    public function store(Request $request)
    {
        return $this->vehicleRepository->insert($request);
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

    public function update(VehicleRequest $vehicleRequest, $id)
    {
        try
        {
            $vehicle = Vehicle::find($id);
            if(is_null($vehicle))
            {
                return $this->userResponse->Failed($vehicle = (object)[],'Not Found.');
            }
            return $this->vehicleRepository->update($vehicleRequest,$id);
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
            $vehicle = Vehicle::find($Id);
            if(is_null($vehicle))
            {
                return $this->userResponse->Failed($vehicle = (object)[],'Not Found.');
            }
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

    public function ActivateDeactivate($Id)
    {
        try
        {
            $vehicle = Vehicle::find($Id);
            if(is_null($vehicle))
            {
                return $this->userResponse->Failed($vehicle = (object)[],'Not Found.');
            }
            $result=$this->vehicleRepository->ActivateDeactivate($Id);
            return $this->userResponse->Success($result);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }
}
