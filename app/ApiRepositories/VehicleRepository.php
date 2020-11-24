<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\IVehicleRepositoryInterface;
use App\Http\Requests\VehicleRequest;
use App\Http\Resources\Vehicle\VehicleResource;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VehicleRepository implements IVehicleRepositoryInterface
{

    public function all()
    {
        return VehicleResource::collection(Vehicle::all()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return VehicleResource::Collection(Vehicle::all()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(Request $request)
    {
        $userId = Auth::id();
        $vehicle = new Vehicle();
        $vehicle->registrationNumber=$request->registrationNumber;
        $vehicle->Description=$request->Description;
        $vehicle->customer_id=$request->customer_id;
        $vehicle->company_id=$request->company_id;
        $vehicle->createdDate=date('Y-m-d h:i:s');
        $vehicle->isActive=1;
        $vehicle->user_id = $userId ?? 0;
        $vehicle->save();
        return new VehicleResource(Vehicle::find($vehicle->id));
    }

    public function update(VehicleRequest $vehicleRequest, $Id)
    {
        $userId = Auth::id();
        $vehicle = Vehicle::find($Id);
        $vehicleRequest['user_id']=$userId ?? 0;
        $vehicle->update($vehicleRequest->all());
        return new VehicleResource(Vehicle::find($Id));
    }

    public function getById($Id)
    {
        return new VehicleResource(Vehicle::find($Id));
    }

    public function delete(Request $request, $Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = Vehicle::find($Id);
        $update->user_id=$userId;
        $update->save();
        $vehicle = Vehicle::withoutTrashed()->find($Id);
        if($vehicle->trashed())
        {
            return new VehicleResource(Vehicle::onlyTrashed()->find($Id));
        }
        else
        {
            $vehicle->delete();
            return new VehicleResource(Vehicle::onlyTrashed()->find($Id));

        }
    }

    public function restore($Id)
    {
        $vehicle = Vehicle::onlyTrashed()->find($Id);
        if (!is_null($vehicle))
        {
            $vehicle->restore();
            return new VehicleResource(Vehicle::find($Id));
        }
        return new VehicleResource(Vehicle::find($Id));
    }

    public function trashed()
    {
        $vehicle = Vehicle::onlyTrashed()->get();
        return VehicleResource::collection($vehicle);
    }
}
