<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\IBankRepositoryInterface;
use App\Http\Requests\BankRequest;
use App\Http\Resources\Bank\BankResource;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function insert(BankRequest $bankRequest)
    {
        $userId = Auth::id();
        $bank = new Bank();
        $bank->Name=$bankRequest->Name;
        $bank->Branch=$bankRequest->Branch;
        $bank->Description=$bankRequest->Description;
        $bank->updateDescription=$bankRequest->updateDescription;
        $bank->contactNumber=$bankRequest->contactNumber;
        $bank->Address=$bankRequest->Address;
        $bank->IsActive=1;
        $bank->user_id = $userId ?? 0;
        $bank->save();
        return new BankResource(Bank::find($bank->id));
    }

    public function update(Request $request, $Id)
    {
        $userId = Auth::id();
        $bank = Bank::find($Id);
        $request['user_id']=$userId ?? 0;
        $bank->update($request->all());
        return new BankResource(Bank::find($Id));
    }

    public function getBankById($Id)
    {
        return new BankResource(Bank::find($Id));
    }

    public function delete(Request $request,$Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = Bank::find($Id);
        $update->user_id=$userId;
        $update->save();
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

    public function ActivateDeactivate($Id)
    {
        $bank = Bank::find($Id);
        if($bank->isActive==1)
        {
            $bank->isActive=0;
        }
        else
        {
            $bank->isActive=1;
        }
        $bank->update();
        return new BankResource(Bank::find($Id));
    }
}
