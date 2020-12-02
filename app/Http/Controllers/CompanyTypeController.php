<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyTypeRequest;
use App\WebRepositories\Interfaces\ICompanyTypeRepositoryInterface;
use Illuminate\Http\Request;

class CompanyTypeController extends Controller
{
    /**
     * @var ICompanyTypeRepositoryInterface
     */
    private $companyTypeRepository;

    public function __construct(ICompanyTypeRepositoryInterface $companyTypeRepository)
    {

        $this->companyTypeRepository = $companyTypeRepository;
    }
    public function index()
    {
        return $this->companyTypeRepository->index();
    }

    public function create()
    {
        return $this->companyTypeRepository->create();
    }

   
    public function store(CompanyTypeRequest $companyTypeRequest)
    {
        return $this->companyTypeRepository->store($companyTypeRequest);
    }

   
    public function show($Id)
    {
        //
    }

    
    public function edit($Id)
    {
        return $this->companyTypeRepository->edit($Id);
    }

    public function update(Request $request, $Id)
    {
        return $this->companyTypeRepository->update($request, $Id);
    }

   
    public function destroy(Request $request, $Id)
    {
        return $this->companyTypeRepository->delete($request, $Id);
    }
}
