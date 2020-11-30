<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\ICompanyRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRequest;
use App\MISC\ServiceResponse;
use App\Models\Company;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;

class CompanyController extends Controller
{
    private $companyRepository;
    private $userResponse;

    public function __construct(ServiceResponse $serviceResponse, ICompanyRepositoryInterface $companyRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->companyRepository=$companyRepository;
    }

    public function index()
    {
        try
        {
            return $this->userResponse->Success($this->companyRepository->all());
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
            return $this->userResponse->Success($this->companyRepository->paginate($page_no,$page_size));
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function store(Request $request)
    {
        return $this->companyRepository->insert($request);
    }

    public function show($id)
    {
        try
        {
            $company = Company::find($id);
            if(is_null($company))
            {
                return $this->userResponse->Failed($company = (object)[],'Not Found.');
            }
            return $this->userResponse->Success($company);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function update(CompanyRequest $companyRequest, $id)
    {
        try
        {
            $company = Company::find($id);
            if(is_null($company))
            {
                return $this->userResponse->Failed($company = (object)[],'Not Found.');
            }
            return $this->companyRepository->update($companyRequest,$id);
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
            $company = Company::find($Id);
            if(is_null($company))
            {
                return $this->userResponse->Failed($company = (object)[],'Not Found.');
            }
            $company = $this->companyRepository->delete($request,$Id);
            return $this->userResponse->Success($company);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function restore($Id)
    {
        try {
            $restore = Company::withTrashed()->where('Id', $Id)->restore();
            return $this->userResponse->Success($restore);

        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public  function  trash()
    {
        $trashed = $this->companyRepository->trashed();
        return $this->userResponse->Success($trashed);
    }
}
