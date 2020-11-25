<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\IEmployeeRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeRquest;
use App\MISC\ServiceResponse;
use App\Models\Employee;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;

class EmployeeController extends Controller
{
    private $employeeRepository;
    private $userResponse;

    public function __construct(ServiceResponse $serviceResponse, IEmployeeRepositoryInterface $employeeRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->employeeRepository=$employeeRepository;
    }

    public function index()
    {
        try
        {
            return $this->userResponse->Success($this->employeeRepository->all());
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
            return $this->userResponse->Success($this->employeeRepository->paginate($page_no,$page_size));
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function store(Request $request)
    {
        return $this->employeeRepository->insert($request);
    }

    public function show($id)
    {
        try
        {
            $employee = Employee::find($id);
            if(is_null($employee))
            {
                return $this->userResponse->Failed($employee = (object)[],'Not Found.');
            }
            return $this->userResponse->Success($employee);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function update(EmployeeRquest $employeeRquest, $id)
    {
        try
        {
            $employee = Employee::find($id);
            if(is_null($employee))
            {
                return $this->userResponse->Failed($employee = (object)[],'Not Found.');
            }
            return $this->employeeRepository->update($employeeRquest,$id);
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
            $employee = Employee::find($Id);
            if(is_null($employee))
            {
                return $this->userResponse->Failed($employee = (object)[],'Not Found.');
            }
            $employee = $this->employeeRepository->delete($request,$Id);
            return $this->userResponse->Success($employee);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function restore($Id)
    {
        try {
            $restore = Employee::withTrashed()->where('Id', $Id)->restore();
            return $this->userResponse->Success($restore);

        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public  function  trash()
    {
        $trashed = $this->employeeRepository->trashed();
        return $this->userResponse->Success($trashed);
    }
}
