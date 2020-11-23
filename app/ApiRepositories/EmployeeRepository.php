<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\IEmployeeRepositoryInterface;
use App\Http\Requests\EmployeeRquest;
use App\Http\Resources\Employee\EmployeeResource;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeRepository implements IEmployeeRepositoryInterface
{
    public function all()
    {
        return EmployeeResource::collection(Employee::all()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return EmployeeResource::Collection(Employee::all()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(Request $request)
    {
        $employee = new Employee();
        $employee->Name=$request->Name;
        $employee->Mobile=$request->Mobile;
        $employee->emergencyContactNumber=$request->emergencyContactNumber;
        $employee->identityNumber=$request->identityNumber;
        $employee->passportNumber=$request->passportNumber;
        $employee->Address=$request->Address;
        $employee->driverLicenceNumber=$request->driverLicenceNumber;
        $employee->driverLicenceExpiry=$request->driverLicenceExpiry;
        $employee->startOfJob=$request->startOfJob;
        $employee->DOB=$request->DOB;
        $employee->Description=$request->Description;
        $employee->company_id=$request->company_id;
        $employee->createdDate=date('Y-m-d h:i:s');
        $employee->isActive=1;
        $employee->user_id = 1;//login user id
        $employee->save();
        return new EmployeeResource(Employee::find($employee->Id));
    }

    public function update(EmployeeRquest $employeeRquest, $Id)
    {
        $employee = Employee::find($Id);
        $employee->update($employeeRquest->all());
        return new EmployeeResource(Employee::find($Id));
    }

    public function getById($Id)
    {
        return new EmployeeResource(Employee::find($Id));
    }

    public function delete(Request $request, $Id)
    {
        $update = Employee::find($Id);
        $update->update($request->all());
        $employee = Employee::withoutTrashed()->find($Id);
        if($employee->trashed())
        {
            return new EmployeeResource(Employee::onlyTrashed()->find($Id));
        }
        else
        {
            $employee->delete();
            return new EmployeeResource(Employee::onlyTrashed()->find($Id));
        }
    }

    public function restore($Id)
    {
        $employee = Employee::onlyTrashed()->find($Id);
        if (!is_null($employee))
        {
            $employee->restore();
            return new EmployeeResource(Employee::find($Id));
        }
        return new EmployeeResource(Employee::find($Id));
    }

    public function trashed()
    {
        $employee = Employee::onlyTrashed()->get();
        return EmployeeResource::collection($employee);
    }
}
