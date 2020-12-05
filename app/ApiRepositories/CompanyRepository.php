<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\ICompanyRepositoryInterface;
use App\Http\Requests\CompanyRequest;
use App\Http\Resources\Company\CompanyResource;
use App\Models\AccountTransaction;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyRepository implements ICompanyRepositoryInterface
{
    public function all()
    {
        return CompanyResource::collection(Company::all()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return CompanyResource::Collection(Company::all()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(CompanyRequest $companyRequest)
    {
        $userId = Auth::id();
        $company = new Company();
        $company->Name=$companyRequest->Name;
        $company->Representative=$companyRequest->Representative;
        $company->Phone=$companyRequest->Phone;
        $company->Mobile=$companyRequest->Mobile;
        $company->Address=$companyRequest->Address;
        $company->postCode=$companyRequest->postCode;
        $company->Description=$companyRequest->Description;
        $company->createdDate=date('Y-m-d h:i:s');
        $company->isActive=1;
        $company->user_id = $userId ?? 0;
        $company->save();

        //create account for newly added customer
        $account_transaction = new AccountTransaction();
        $account_transaction->Credit=0.00;
        $account_transaction->Debit=0.00;
        $account_transaction->company_id=$company->id;
        $account_transaction->user_id=$userId ?? 0;
        $account_transaction->Description='account created';
        $account_transaction->createdDate=date('Y-m-d h:i:s');
        $account_transaction->save();

        return new CompanyResource(Company::find($company->id));
    }

    public function update(Request $request, $Id)
    {
        $userId = Auth::id();
        $company = Company::find($Id);
        $request['user_id']=$userId ?? 0;
        $company->update($request->all());
        return new CompanyResource(Company::find($Id));
    }

    public function getById($Id)
    {
        return new CompanyResource(Company::find($Id));
    }

    public function delete(Request $request, $Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = Company::find($Id);
        $update->user_id=$userId;
        $update->save();
        $company = Company::withoutTrashed()->find($Id);
        if($company->trashed())
        {
            return new CompanyResource(Company::onlyTrashed()->find($Id));
        }
        else
        {
            $company->delete();
            return new CompanyResource(Company::onlyTrashed()->find($Id));
        }
    }

    public function restore($Id)
    {
        $company = Company::onlyTrashed()->find($Id);
        if (!is_null($company))
        {
            $company->restore();
            return new CompanyResource(Company::find($Id));
        }
        return new CompanyResource(Company::find($Id));
    }

    public function trashed()
    {
        $company = Company::onlyTrashed()->get();
        return CompanyResource::collection($company);
    }

    public function ActivateDeactivate($Id)
    {
        $company = Company::find($Id);
        if($company->isActive==1)
        {
            $company->isActive=0;
        }
        else
        {
            $company->isActive=1;
        }
        $company->update();
        return new CompanyResource(Company::find($Id));
    }
}
