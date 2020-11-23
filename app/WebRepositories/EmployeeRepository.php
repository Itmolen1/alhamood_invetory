<?php


namespace App\WebRepositories;


use App\Http\Requests\EmployeeRquest;
use App\Models\Employee;
use App\Models\Region;
use App\WebRepositories\Interfaces\IEmployeeRepositoryInterface;
use Illuminate\Http\Request;

class EmployeeRepository implements IEmployeeRepositoryInterface
{

    public function index()
    {
        // TODO: Implement index() method.
        $employees = Employee::all();
        return view('admin.employee.index',compact('employees'));
    }

    public function create()
    {
        // TODO: Implement create() method.
        $regions = Region::all();
        return view('admin.employee.create',compact('regions'));
    }

    public function store(EmployeeRquest $employeeRequest)
    {
        // TODO: Implement store() method.
        $user_id = session('user_id');
        $company_id = session('company_id');
        $data = [
            'Name' => $employeeRequest->Name,
            'Mobile' => $employeeRequest->Mobile,
            'emergencyContactNumber' => $employeeRequest->emergencyContactNumber,
            'passportNumber' => $employeeRequest->passportNumber,
            'Address' => $employeeRequest->Address,
            'region_id' => $employeeRequest->region_id,
            'Description' => $employeeRequest->Description,
            'user_id' => $user_id,
            'company_id' => $company_id,
        ];
        Employee::create($data);
        return redirect()->route('employees.index')->with('success','Record Inserted Successfully');
    }

    public function update(Request $request, $Id)
    {
        // TODO: Implement update() method.
        $user_id = session('user_id');
        $company_id = session('company_id');
        $data = Employee::find($Id);
        $data->update([
            'Name' => $request->Name,
            'Mobile' => $request->Mobile,
            'emergencyContactNumber' => $request->emergencyContactNumber,
            'passportNumber' => $request->passportNumber,
            'Address' => $request->Address,
            'region_id' => $request->region_id,
            'Description' => $request->Description,
            'user_id' => $user_id,
            'company_id' => $company_id,
        ]);
        return redirect()->route('employees.index')->with('update','Record Updated Successfully');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        // TODO: Implement edit() method.
        $regions = Region::all();
        $employee = Employee::with('region')->find($Id);
        return view('admin.employee.edit',compact('regions','employee'));
    }

    public function delete(Request $request, $Id)
    {
        // TODO: Implement delete() method.
        $Update = Employee::find($Id);
        $user_id = session('user_id');
        $company_id = session('company_id');
        $Update->update([
            'user_id' => $user_id,
            'company_id' => $company_id,
        ]);
        $state = Employee::withoutTrashed()->find($Id);
        if($state->trashed())
        {
            return redirect()->route('employees.index');
        }
        else
        {
            $state->delete();
            return redirect()->route('employees.index')->with('delete','Record Update Successfully');
        }

    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
        $state = Employee::onlyTrashed()->find($Id);
        if (!is_null($state))
        {
            $state->restore();
            return redirect()->route('employees.index')->with('restore','Record Restore Successfully');
        }
        return redirect()->route('employees.index');
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
        $trashes = Employee::with('user')->onlyTrashed()->get();
        return view('admin.employees.edit',compact('trashes'));
    }
}
