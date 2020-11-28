<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\ICompanyRepositoryInterface;
use App\Http\Requests\CompanyRequest;
use App\Http\Resources\Company\CompanyResource;
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

    public function insert(Request $request)
    {
        $userId = Auth::id();
        $company = new Company();
        $company->Name=$request->Name;
        $company->Representative=$request->Representative;
        $company->Phone=$request->Phone;
        $company->Mobile=$request->Mobile;
        $company->Address=$request->Address;
        $company->postCode=$request->postCode;
        $company->Description=$request->Description;
        $company->createdDate=date('Y-m-d h:i:s');
        $company->isActive=1;
        $company->user_id = $userId ?? 0;
        $company->save();
        return new CompanyResource(Company::find($company->id));
    }

    public function update(CompanyRequest $companyRequest, $Id)
    {
        $userId = Auth::id();
        $company = Company::find($Id);
        $companyRequest['user_id']=$userId ?? 0;
        $company->update($companyRequest->all());
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
}
