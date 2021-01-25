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
        // TODO: Implement index() method.
        // $banks = Bank::with('user','company')->get();
        // return view('admin.bank.index',compact('banks'));
        if(request()->ajax())
        {
            return datatables()->of(Bank::with('user','company')->latest()->get())
               ->addColumn('action', function ($data) {
                    $button = '<form action="'.route('banks.destroy', $data->id).'" method="POST"  id="deleteData">';
                    $button .= @csrf_field();
                    $button .= @method_field('DELETE');
                    $button .= '<a href="'.route('banks.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" class=" btn btn-danger btn-sm" onclick="ConfirmDelete()"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                    $button .= '</form>';
                    return $button;
                })
                ->addColumn('isActive', function($data) {
                        if($data->isActive == true){
                            $button = '<form action="'.route('banks.destroy', $data->id).'" method="POST"  id="deleteData">';
                            $button .= @csrf_field();
                            $button .= @method_field('PUT');
                            $button .= '<label class="switch"><input name="isActive" id="isActive" type="checkbox" checked><span class="slider"></span></label>';
                            return $button;
                        }else{
                            $button = '<form action="'.route('banks.destroy', $data->id).'" method="POST"  id="deleteData">';
                            $button .= @csrf_field();
                            $button .= @method_field('PUT');
                            $button .= '<label class="switch"><input name="isActive" id="isActive" type="checkbox" checked><span class="slider"></span></label>';
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
        // TODO: Implement create() method.
        return view('admin.bank.create');
    }

    public function store(BankRequest $bankRequest)
    {
        $user_id = session('user_id');
        $company_id = session('company_id');
        $bank = [
            'Name' =>$bankRequest->Name,
            'Branch' =>$bankRequest->Branch,
            'openingBalance' =>$bankRequest->openingBalance,
            'openingBalanceAsOfDate' =>$bankRequest->openingBalanceAsOfDate,
            'Description' =>$bankRequest->Description,
            'contactNumber' =>$bankRequest->contactNumber,
            'Address' =>$bankRequest->Address,
            'user_id' =>$user_id,
            'company_id' =>$company_id,
        ];
        Bank::create($bank);

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
            ]);
        }

        return redirect()->route('banks.index');
    }

    public function update(Request $request, $Id)
    {
        // TODO: Implement update() method.
        $bank = Bank::find($Id);
        $user_id = session('user_id');
        $bank->update([
            'Name' =>$request->Name,
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
        // TODO: Implement edit() method.
        $bank = Bank::find($Id);
        return view('admin.bank.edit',compact('bank'));
    }

    public function delete(Request $request, $Id)
    {
        // TODO: Implement delete() method.
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
