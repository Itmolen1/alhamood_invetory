<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\ICompanyTypeRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyTypeRequest;
use App\MISC\ServiceResponse;
use App\Models\CompanyType;
use Illuminate\Http\Request;

class CompanyTypeController extends Controller
{
    private $userResponse;
    private $companyTypeRepository;

    public function __construct(ServiceResponse $serviceResponse, ICompanyTypeRepositoryInterface $companyTypeRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->companyTypeRepository=$companyTypeRepository;
    }

    public function index()
    {
        try
        {
            return $this->userResponse->Success($this->companyTypeRepository->all());
        }
        catch (Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function paginate($page_no,$page_size)
    {
        try
        {
            return $this->userResponse->Success($this->companyTypeRepository->paginate($page_no,$page_size));
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function store(Request $request)
    {
        return $this->companyTypeRepository->insert($request);
    }

    public function show($id)
    {
        try
        {
            $company_type = CompanyType::find($id);
            if(is_null($company_type))
            {
                return $this->userResponse->Failed($company_type = (object)[],'Not Found.');
            }
            return $this->userResponse->Success($company_type);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function update(CompanyTypeRequest $companyTypeRequest, $id)
    {
        try
        {
            $company_type = CompanyType::find($id);
            if(is_null($company_type))
            {
                return $this->userResponse->Failed($company_type = (object)[],'Not Found.');
            }
            return $this->companyTypeRepository->update($companyTypeRequest,$id);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function destroy(Request $request,$Id)
    {
        try
        {
            $company_type = CompanyType::find($Id);
            if(is_null($company_type))
            {
                return $this->userResponse->Failed($company_type = (object)[],'Not Found.');
            }
            $company_type = $this->companyTypeRepository->delete($request,$Id);
            return $this->userResponse->Success($company_type);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function restore($Id)
    {
        try {
            $restore = CompanyType::withTrashed()->where('Id', $Id)->restore();
            return $this->userResponse->Success($restore);

        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public  function  trash()
    {
        $trashed = $this->companyTypeRepository->trashed();
        return $this->userResponse->Success($trashed);
    }

    public function ActivateDeactivate($Id)
    {
        try
        {
            $company_type = CompanyType::find($Id);
            if(is_null($company_type))
            {
                return $this->userResponse->Failed($company_type = (object)[],'Not Found.');
            }
            $result=$this->companyTypeRepository->ActivateDeactivate($Id);
            return $this->userResponse->Success($result);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }
}
