<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\IEmployeeRepositoryInterface;
use App\Http\Requests\EmployeeRquest;
use App\Http\Resources\Employee\EmployeeResource;
use App\Models\AccountTransaction;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
        $userId = Auth::id();
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
        //$employee->company_id=$request->company_id;
        $employee->createdDate=date('Y-m-d h:i:s');
        $employee->isActive=1;
        $employee->user_id = $userId ?? 0;
        $employee->company_id=Str::getCompany($userId);
        $employee->save();

        //create account for newly added customer
        $account_transaction = new AccountTransaction();
        $account_transaction->Credit=0.00;
        $account_transaction->Debit=0.00;
        $account_transaction->employee_id=$employee->id;
        $account_transaction->user_id=$userId ?? 0;
        $account_transaction->Description='account created';
        $account_transaction->createdDate=date('Y-m-d h:i:s');
        $account_transaction->save();

        return new EmployeeResource(Employee::find($employee->id));
    }

    public function update(EmployeeRquest $employeeRquest, $Id)
    {
        $userId = Auth::id();
        $employee = Employee::find($Id);
        $employeeRquest['user_id']=$userId ?? 0;
        $employee->update($employeeRquest->all());
        return new EmployeeResource(Employee::find($Id));
    }

    public function getById($Id)
    {
        return new EmployeeResource(Employee::find($Id));
    }

    public function delete(Request $request, $Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = Employee::find($Id);
        $update->user_id=$userId;
        $update->save();
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

    public function ActivateDeactivate($Id)
    {
        $employee = Employee::find($Id);
        if($employee->isActive==1)
        {
            $employee->isActive=0;
        }
        else
        {
            $employee->isActive=1;
        }
        $employee->update();
        return new EmployeeResource(Employee::find($Id));
    }
}
