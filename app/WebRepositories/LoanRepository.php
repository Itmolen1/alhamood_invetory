<?php


namespace App\WebRepositories;


use App\Models\Customer;
use App\Models\Employee;
use App\Models\Loan;
use App\WebRepositories\Interfaces\ILoanRepositoryInterface;
use Illuminate\Http\Request;

class LoanRepository implements ILoanRepositoryInterface
{

    public function index()
    {
        // TODO: Implement index() method.
        $loans = Loan::with('customer','employee','user')->get();
        return view('admin.loan.index',compact('loans'));
    }

    public function create()
    {
        // TODO: Implement create() method.
        $customers = Customer::all();
        $employees = Employee::all();
        return view('admin.loan.create',compact('customers','employees'));
    }

    public function store(Request $request)
    {

        // TODO: Implement store() method.
        $user_id = session('user_id');
        $company_id = session('company_id');
        if ($request->input('loanPayment') == 'isPay')
        {
            $isPay = true;
            $isReturn = false;
                if ($request->input('loanTo') == 'customer') {
                $customer = $request->customer_id;
                $employee = 0;
                $remainingLoan =  $request->payLoan + $request->remainingLoan;
                }
                elseif ($request->input('loanTo')  == 'employee') {
                     $customer = 0;
                     $employee = $request->employee_id;
                     $remainingLoan =  $request->payLoan + $request->remainingLoan;
                }
        }
        elseif ($request->input('loanPayment') == 'isReturn')
        {
            $isPay = false;
            $isReturn = true;
                if ($request->input('loanTo') == 'customer') {
                $customer = $request->customer_id;
                $employee = 0;
                $remainingLoan =  $request->remainingLoan - $request->payLoan;
                }
                elseif ($request->input('loanTo')  == 'employee') {
                     $customer = 0;
                     $employee = $request->employee_id;
                     $remainingLoan = $request->remainingLoan - $request->payLoan;
                }
        }

        
        $data = [
            'loanTo' => $request->loanTo,
            'employee_id' => $employee,
            'customer_id' => $customer,
            'isPay' => $isPay,
            'isReturn' => $isReturn,
            'user_id' => $user_id,
            'company_id' => $company_id,
            'loanDate' => $request->loanDate,
            'remainingLoan' => $remainingLoan,
            'payLoan' => $request->payLoan,
            'loanInWords' => $request->loanInWords,
            'voucherNumber' => $request->voucherNumber,
            'Description' => $request->Description
        ];
        Loan::create($data);
        return redirect()->route('loans.index')->with('success','Record Inserted Successfully');
    }

    public function update(Request $request, $Id)
    {
        // TODO: Implement update() method.
        $totalRemaining = Loan::where('id', $Id)->first();
        $remaingValue = $totalRemaining->remainingLoan - $totalRemaining->payLoan;

        if ($totalRemaining->isReturn == true) {

            if ($request->input('loanPayment') == 'isPay')
            {
                $remaingValue = $totalRemaining->remainingLoan;
            }
            else
            {
                $remaingValue = $totalRemaining->remainingLoan + $totalRemaining->payLoan;
            }
        }
        else if($totalRemaining->isPay == true)
        {   
             if ($request->input('loanPayment') == 'isReturn')
            {
                $remaingValue = $totalRemaining->remainingLoan;
            }
            else
            {
                $remaingValue = $totalRemaining->remainingLoan - $totalRemaining->payLoan;
            }
        }

        //dd($request);
        $user_id = session('user_id');
        $company_id = session('company_id');
        if ($request->input('loanPayment') == 'isPay')
        {
            $isPay = true;
            $isReturn = false;
                if ($request->input('loanTo') == 'customer') {
                $customer = $request->customer_id;
                $employee = 0;
                $remainingLoan =  $request->payLoan + $remaingValue;
                }
                elseif ($request->input('loanTo')  == 'employee') {
                     $customer = 0;
                     $employee = $request->employee_id;
                     $remainingLoan =  $request->payLoan + $remaingValue;
                }
        }
        elseif ($request->input('loanPayment') == 'isReturn')
        {
            $isPay = false;
            $isReturn = true;
                if ($request->input('loanTo') == 'customer') {
                $customer = $request->customer_id;
                $employee = 0;
                $remainingLoan =  $remaingValue - $request->payLoan;
                }
                elseif ($request->input('loanTo')  == 'employee') {
                     $customer = 0;
                     $employee = $request->employee_id;
                     $remainingLoan = $remaingValue - $request->payLoan;
                }
        }   
        $data = Loan::find($Id);
        $data->update([
                'loanTo' => $request->loanTo,
                'employee_id' => $employee,
                'customer_id' => $customer,
                'user_id' => $user_id,
                'company_id' => $company_id,
                'Description' => $request->Description,
                'payLoan' => $request->payLoan,
                'loanInWords' => $request->loanInWords,
                'voucherNumber' => $request->voucherNumber,
                'remainingLoan' => $remainingLoan,
                'isPay' => $isPay,
                'isReturn' => $isReturn,
                'loanDate' => $request->loanDate
        ]);
        return redirect()->route('loans.index')->with('update','updated Successfully');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        // TODO: Implement edit() method.
        $customers = Customer::all();
        $employees = Employee::all();
        $loan = Loan::with('customer','employee','user')->find($Id);
        return view('admin.loan.edit',compact('customers','employees','loan'));
    }

    public function delete(Request $request, $Id)
    {
        // TODO: Implement delete() method.
        $Update = Loan::find($Id);
        $user_id = session('user_id');
        $company_id = session('company_id');
        $Update->update([
            'user_id' => $user_id,
            'company_id' => $company_id,
        ]);
        $state = Loan::withoutTrashed()->find($Id);
        if($state->trashed())
        {
            return redirect()->route('loans.index');
        }
        else
        {
            $state->delete();
            return redirect()->route('loans.index')->with('delete','Record Update Successfully');
        }
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
        $loan = Loan::onlyTrashed()->find($Id);
        if (!is_null($loan))
        {
            $loan->restore();
            return redirect()->route('loans.index')->with('restore','Record Restore Successfully');
        }
        return redirect()->route('loans.index');
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
        $trashes = Loan::with('user')->onlyTrashed()->get();
        return view('admin.loan.trash',compact('trashes'));
    }

    public function customerRemaining($Id)
    {
        $customers = Loan::with('customer')->where(['customer_id'=> $Id])->get();

        //return Response()->json($dd);
        if ($customers != null)
        {

            if ($customers->isEmpty()) 
            {
                    //$data = $customers->sum('remainingLoan');
                    return Response()->json($customers);
            }
                else
                {
                    $pay = $customers->where('isPay', true);
                    $return = $customers->where('isReturn', true);
                    $dataPay = $pay->sum('payLoan');
                    $dataReturn = $return->sum('payLoan');
                    $TotalRemaining = $dataPay - $dataReturn;
                    return Response()->json($TotalRemaining);
                }
        }
        else
        {
           return Response()->json($customers);
        }
    }

    public function employeeRemaining($Id)
    {
        $employees = Loan::with('employee')->where(['employee_id'=> $Id])->get();
        //return Response()->json($employees);
        if ($employees != null)
        {
            if ($employees->isEmpty()) 
            {
                    //$data = $employees->sum('remainingLoan');
                    return Response()->json($employees);
            }
                else
                {
                    // $data = $employees->last()->remainingLoan;
                    // return Response()->json($data);
                    $pay = $employees->where('isPay', true);
                    $return = $employees->where('isReturn', true);
                    $dataPay = $pay->sum('payLoan');
                    $dataReturn = $return->sum('payLoan');
                    $TotalRemaining = $dataPay - $dataReturn;
                    return Response()->json($TotalRemaining);

                }
        }
        else
        {
           return Response()->json($employees);
        }
    }


}
