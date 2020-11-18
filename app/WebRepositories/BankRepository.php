<?php


namespace App\WebRepositories;


use App\Http\Requests\BankRequest;
use App\Models\Bank;
use App\WebRepositories\Interfaces\IBankRepositoryInterface;
use Illuminate\Http\Request;

class BankRepository implements IBankRepositoryInterface
{

    public function index()
    {
        // TODO: Implement index() method.
        $banks = Bank::with('user','company')->get();
        return view('admin.bank.index',compact('banks'));
    }

    public function create()
    {
        // TODO: Implement create() method.
        return view('admin.bank.create');
    }

    public function store(BankRequest $bankRequest)
    {
        // TODO: Implement store() method.
        $user_id = session('user_id');
        $company_id = session('company_id');
        $role = [
            'Name' =>$bankRequest->Name,
            'Branch' =>$bankRequest->Branch,
            'Description' =>$bankRequest->Description,
            'contactNumber' =>$bankRequest->contactNumber,
            'Address' =>$bankRequest->Address,
            'user_id' =>$user_id,
            'company_id' =>$company_id,
        ];
        Bank::create($role);
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
            'Description' =>$request->Description,
            'contactNumber' =>$request->contactNumber,
            'Address' =>$request->Address,
            'user_id' =>$user_id,
        ]);
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
