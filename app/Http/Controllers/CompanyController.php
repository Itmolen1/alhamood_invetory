<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use App\WebRepositories\Interfaces\ICompanyRepositoryInterface;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * @var ICompanyRepositoryInterface
     */
    private $companyRepository;

    public function __construct(ICompanyRepositoryInterface $companyRepository)
    {
        $this->companyRepository = $companyRepository;
    }

    public function index()
    {
        return $this->companyRepository->index();
    }

    public function create()
    {
        return $this->companyRepository->create();
    }


    public function store(CompanyRequest $companyRequest)
    {
        return $this->companyRepository->store($companyRequest);
    }


    public function show(Company $company)
    {
        //
    }


    public function edit($Id)
    {
        return $this->companyRepository->edit($Id);
    }


    public function update(Request $request, $Id)
    {
        return $this->companyRepository->update($request, $Id);
    }


    public function destroy(Request $request, $Id)
    {
        return $this->companyRepository->delete($request, $Id);
    }
}
