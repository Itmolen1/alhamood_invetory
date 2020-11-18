<?php


namespace App\WebRepositories;


use App\Http\Requests\VehicleRequest;
use App\Models\Customer;
use App\Models\Vehicle;
use App\WebRepositories\Interfaces\IVehicleRepositoryInterface;
use Illuminate\Http\Request;

class VehicleRepository implements IVehicleRepositoryInterface
{

    public function index()
    {
        // TODO: Implement index() method.
        $vehicles = Vehicle::with('customer')->get();
        return view('admin.vehicle.index',compact('vehicles'));
    }

    public function create()
    {
        // TODO: Implement create() method.
        $customers = Customer::all();
        return view('admin.vehicle.create',compact('customers'));
    }

    public function store(VehicleRequest $vehicleRequest)
    {
        // TODO: Implement store() method.
        $user_id = session('user_id');
        $company_id = session('company_id');

        $vehicle = [
            'registrationNumber' =>$vehicleRequest->registrationNumber,
            'user_id' =>$user_id,
            'company_id' =>$company_id,
            'customer_id' =>$vehicleRequest->customer_id,
            'Description' =>$vehicleRequest->Description,
        ];
        Vehicle::create($vehicle);
        return redirect()->route('vehicles.index');
    }

    public function update(Request $request, $Id)
    {
        // TODO: Implement update() method.
        $vehicle = Vehicle::find($Id);

        $user_id = session('user_id');
        $vehicle->update([
            'registrationNumber' =>$request->registrationNumber,
            'user_id' =>$user_id,
            'customer_id' =>$request->customer_id,
            'Description' =>$request->Description,

        ]);
        return redirect()->route('vehicles.index');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.

    }

    public function edit($Id)
    {
        // TODO: Implement edit() method.
        $customers = Customer::all();
        $vehicle = Vehicle::with('customer')->find($Id);
        return view('admin.vehicle.edit',compact('customers','vehicle'));
    }

    public function delete(Request $request, $Id)
    {
        // TODO: Implement delete() method.
        $data = Vehicle::findOrFail($Id);
        $data->delete();
        return redirect()->route('vehicles.index');
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
    }
}
