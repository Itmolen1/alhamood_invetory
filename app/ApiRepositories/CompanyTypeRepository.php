<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\ICompanyTypeRepositoryInterface;
use App\Http\Requests\CompanyTypeRequest;
use App\Http\Resources\CompanyType\CompanyTypeResource;
use App\Models\CompanyType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CompanyTypeRepository implements ICompanyTypeRepositoryInterface
{
    public function all()
    {
        return CompanyTypeResource::collection(CompanyType::all()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return CompanyTypeResource::Collection(CompanyType::all()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(Request $request)
    {
        $userId = Auth::id();
        $company_type = new CompanyType();
        $company_type->Name=$request->Name;
        $company_type->Description=$request->Description;
        $company_type->createdDate=date('Y-m-d h:i:s');
        $company_type->isActive=1;
        $company_type->user_id = $userId ?? 0;
        $company_type->company_id=Str::getCompany($userId);
        $company_type->save();
        return new CompanyTypeResource(CompanyType::find($company_type->id));
    }

    public function update(CompanyTypeRequest $companyTypeRequest, $Id)
    {
        $userId = Auth::id();
        $company_type = CompanyType::find($Id);
        $companyTypeRequest['user_id']=$userId ?? 0;
        $company_type->update($companyTypeRequest->all());
        return new CompanyTypeResource(CompanyType::find($Id));
    }

    public function getById($Id)
    {
        return new CompanyTypeResource(CompanyType::find($Id));
    }

    public function delete(Request $request, $Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = CompanyType::find($Id);
        $update->user_id=$userId;
        $update->save();
        $company_type = CompanyType::withoutTrashed()->find($Id);
        if($company_type->trashed())
        {
            return new CompanyTypeResource(CompanyType::onlyTrashed()->find($Id));
        }
        else
        {
            $company_type->delete();
            return new CompanyTypeResource(CompanyType::onlyTrashed()->find($Id));
        }
    }

    public function restore($Id)
    {
        $company_type = CompanyType::onlyTrashed()->find($Id);
        if (!is_null($company_type))
        {
            $company_type->restore();
            return new CompanyTypeResource(CompanyType::find($Id));
        }
        return new CompanyTypeResource(CompanyType::find($Id));
    }

    public function trashed()
    {
        $company_type = CompanyType::onlyTrashed()->get();
        return CompanyTypeResource::collection($company_type);
    }

    public function ActivateDeactivate($Id)
    {
        $company_type = CompanyType::find($Id);
        if($company_type->isActive==1)
        {
            $company_type->isActive=0;
        }
        else
        {
            $company_type->isActive=1;
        }
        $company_type->update();
        return new CompanyTypeResource(CompanyType::find($Id));
    }
}
