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
        if(request()->ajax())
        {
            return datatables()->of(Vehicle::with('customer')->latest()->get())
                ->addColumn('action', function ($data) {
                    $button = '<form action="'.route('vehicles.destroy', $data->id).'" method="POST">';
                    $button .= @csrf_field();
                    $button .= @method_field('DELETE');
                    $button .= '<a href="'.route('vehicles.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" class=" btn btn-danger btn-sm" onclick="ConfirmDelete()"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                    $button .= '</form>';
                    return $button;
                })
                ->addColumn('customerName', function($data) {
                    return $data->customer->Name ?? "";
                })
                ->addColumn('Description', function($data) {
                    return $data->Description ?? "a";
                })
                ->rawColumns([
                    'action',
                    'isActive',
                    'customerName',
                    'Description',
                ])
                ->make(true);
        }
        //$vehicles = Vehicle::with('customer')->get();
        return view('admin.vehicle.index');
    }

    public function create()
    {
        $customers = Customer::all();
        return view('admin.vehicle.create',compact('customers'));
    }

    public function store(VehicleRequest $vehicleRequest)
    {
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
        $customers = Customer::all();
        $vehicle = Vehicle::with('customer')->find($Id);
        return view('admin.vehicle.edit',compact('customers','vehicle'));
    }

    public function delete(Request $request, $Id)
    {
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
