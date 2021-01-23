<?php


namespace App\WebRepositories;


use App\Http\Requests\EmployeeRquest;
use App\Models\Employee;
use App\Models\Region;
use App\WebRepositories\Interfaces\IEmployeeRepositoryInterface;
use Illuminate\Http\Request;
use App\Models\AccountTransaction;

class EmployeeRepository implements IEmployeeRepositoryInterface
{

    public function index()
    {
        // TODO: Implement index() method.
        // $employees = Employee::all();
        // return view('admin.employee.index',compact('employees'));
        if(request()->ajax())
        {
            return datatables()->of(Employee::latest()->get())
               ->addColumn('action', function ($data) {
                    $button = '<form action="'.route('employees.destroy', $data->id).'" method="POST"  id="deleteData">';
                    $button .= @csrf_field();
                    $button .= @method_field('DELETE');
                    $button .= '<a href="'.route('employees.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" class=" btn btn-danger btn-sm" onclick="ConfirmDelete()"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                    $button .= '</form>';
                    return $button;
                })
                ->addColumn('isActive', function($data) {
                        if($data->isActive == true){
                            $button = '<form action="'.route('employees.update', $data->id).'" method="POST"  id="deleteData">';
                            $button .= @csrf_field();
                            $button .= @method_field('PUT');
                            $button .= '<label class="switch"><input name="isActive" id="isActive" type="checkbox" checked><span class="slider"></span></label>';
                            return $button;
                        }else{
                            $button = '<form action="'.route('employees.update', $data->id).'" method="POST"  id="deleteData">';
                            $button .= @csrf_field();
                            $button .= @method_field('PUT');
                            $button .= '<label class="switch"><input name="isActive" id="isActive" type="checkbox" checked><span class="slider"></span></label>';
                            return $button;
                        }
                    })
                 // ->addColumn('state.Name', function($data) {
                 //        return $data->state->Name ?? "No State";
                 //    })
                ->rawColumns([
                    'action',
                    'isActive',
                    // 'state.Name'
                ])
                ->make(true);
        }
        return view('admin.employee.index');
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
            'region_id' => $employeeRequest->region_id ?? 0,
            'Description' => $employeeRequest->Description,
            'user_id' => $user_id ?? 0,
            'company_id' => $company_id ?? 0,
        ];
        $employee = Employee::create($data);
        if ($employee) {
            $account = new AccountTransaction([
                'employee_id' => $employee->id ?? 0,
                'user_id' => $user_id ?? 0,
                'company_id' => $company_id ?? 0,
                'Description' => 'initial',
            ]);
        }
        $employee->account_transaction()->save($account);
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
            'region_id' => $request->region_id ?? 0,
            'Description' => $request->Description,
            'user_id' => $user_id ?? 0,
            'company_id' => $company_id ?? 0,
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
