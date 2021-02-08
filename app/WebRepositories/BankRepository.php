<?php


namespace App\WebRepositories;


use App\Http\Requests\BankRequest;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\WebRepositories\Interfaces\IBankRepositoryInterface;
use Illuminate\Http\Request;

class BankRepository implements IBankRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(Bank::with('user','company')->latest()->get())
               ->addColumn('action', function ($data) {
                    $button = '<form action="'.route('banks.destroy', $data->id).'" method="POST">';
                    $button .= @method_field('DELETE');
                    $button .= @csrf_field();
                    $button .= '<button type="submit" class=" btn btn-danger btn-sm" onclick="return ConfirmDelete()"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<a href="'.route('banks.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button .= '</form>';
                    return $button;
                })
                ->addColumn('isActive', function($data) {
                        if($data->isActive == true){
                            $button = '<form action="'.route('banks.destroy', $data->id).'" method="POST" >';
                            $button .= @csrf_field();
                            $button .= @method_field('PUT');
                            $button .= '<label class="switch"><input name="isActive" type="checkbox" checked><span class="slider"></span></label>';
                            return $button;
                        }else{
                            $button = '<form action="'.route('banks.destroy', $data->id).'" method="POST" >';
                            $button .= @csrf_field();
                            $button .= @method_field('PUT');
                            $button .= '<label class="switch"><input name="isActive"  type="checkbox" checked><span class="slider"></span></label>';
                            return $button;
                        }
                    })
                ->rawColumns(['action','isActive'])
                ->make(true);
        }
        return view('admin.bank.index');
    }

    public function create()
    {
        return view('admin.bank.create');
    }

    public function store(BankRequest $bankRequest)
    {
        $user_id = session('user_id');
        $company_id = session('company_id');
        $bank = [
            'Name' =>strip_tags($bankRequest->Name),
            'Branch' =>strip_tags($bankRequest->Branch),
            'openingBalance' =>$bankRequest->openingBalance,
            'openingBalanceAsOfDate' =>$bankRequest->openingBalanceAsOfDate,
            'Description' =>$bankRequest->Description,
            'contactNumber' =>$bankRequest->contactNumber,
            'Address' =>$bankRequest->Address,
            'user_id' =>$user_id,
            'company_id' =>$company_id,
        ];
        $bank=Bank::create($bank);

        //initial cash or cash on hand for the company
        if ($bank) {
            BankTransaction::Create([
                'Reference' => $bank->id,
                'user_id' => $user_id,
                'createdDate' => $bankRequest->openingBalanceAsOfDate,
                'company_id' =>$company_id,
                'Details' =>'initial',
                'Credit' =>0.00,
                'Debit' =>0.00,
                'Differentiate' =>$bankRequest->openingBalance,
                'bank_id' =>$bank->id,
            ]);
        }
        return redirect()->route('banks.index');
    }

    public function update(Request $request, $Id)
    {
        $bank = Bank::find($Id);
        $user_id = session('user_id');
        $bank->update([
            'Name' =>strip_tags($request->Name),
            'Branch' =>$request->Branch,
            'openingBalance' =>$request->openingBalance,
            'openingBalanceAsOfDate' =>$request->openingBalanceAsOfDate,
            'Description' =>$request->Description,
            'contactNumber' =>$request->contactNumber,
            'Address' =>$request->Address,
            'user_id' =>$user_id,
        ]);

        //initial cash or cash on hand for the company
        $company_id = session('company_id');
        if ($bank) {
            BankTransaction::Create([
                'Reference' => $bank->id,
                'user_id' => $user_id,
                'createdDate' => $request->openingBalanceAsOfDate,
                'company_id' =>$company_id,
                'Details' =>'initial',
                'Credit' =>0.00,
                'Debit' =>0.00,
                'Differentiate' =>$request->openingBalance,
                'bank_id' => $Id,
            ]);
        }
        return redirect()->route('banks.index');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        $bank = Bank::find($Id);
        return view('admin.bank.edit',compact('bank'));
    }

    public function delete(Request $request, $Id)
    {
        $data = Bank::findOrFail($Id);
        $data->delete();
        return redirect()->route('banks.index');
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
