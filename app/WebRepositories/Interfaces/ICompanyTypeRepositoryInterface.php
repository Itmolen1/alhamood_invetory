<?php


namespace App\WebRepositories\Interfaces;


use App\Http\Requests\CompanyTypeRequest;
use Illuminate\Http\Request;

interface ICompanyTypeRepositoryInterface
{

    public function index();

    public function create();

    public function store(CompanyTypeRequest $companyTypeRequest);

    public function update(Request $request, $Id);

    public function getById($Id);

    public function edit($Id);

    public function delete(Request $request, $Id);

    public function  restore($Id);

    public function trashed();

}
