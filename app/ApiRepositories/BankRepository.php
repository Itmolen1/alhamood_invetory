<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\IBankRepositoryInterface;
use App\Http\Requests\BankRequest;
use App\Http\Resources\Bank\BankResource;
use App\Models\Bank;
use Illuminate\Http\Request;

class BankRepository implements IBankRepositoryInterface
{

    public function all()
    {
        return BankResource::collection(Bank::all()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return BankResource::Collection(Bank::all()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(Request $request)
    {
        $bank = new Bank();
        $bank->Name=$request->Name;
        $bank->Branch=$request->Branch;
        $bank->Description=$request->Description;
        $bank->updateDescription=$request->updateDescription;
        $bank->contactNumber=$request->contactNumber;
        $bank->Address=$request->Address;
        $bank->IsActive=$request->IsActive;
        $bank->user_id = $request->user_id;
        $bank->save();
        return new BankResource(Bank::find($bank->Id));
    }

    public function update(BankRequest $bankRequest, $Id)
    {
        $bank = Bank::find($Id);
        $bank->update($bankRequest->all());
        return new BankResource(Bank::find($Id));
    }

    public function getBankById($Id)
    {
        return new BankResource(Bank::find($Id));
    }

    public function delete(Request $request, $Id)
    {
        $update = Bank::find($Id);
        $update->update($request->all());
        $bank = Bank::withoutTrashed()->find($Id);
        if($bank->trashed())
        {
            return new BankResource(Bank::onlyTrashed()->find($Id));
        }
        else
        {
            $bank->delete();
            return new BankResource(Bank::onlyTrashed()->find($Id));

        }
    }

    public function restore($Id)
    {
        $bank = Bank::onlyTrashed()->find($Id);
        if (!is_null($bank))
        {
            $bank->restore();
            return new BankResource(Bank::find($Id));
        }
        return new BankResource(Bank::find($Id));
    }

    public function trashed()
    {
        $bank = Bank::onlyTrashed()->get();
        return BankResource::collection($bank);
    }
}
