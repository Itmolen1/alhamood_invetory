<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\WebRepositories\Interfaces\IInwardLoanRepositoryInterface;
use Illuminate\Http\Request;

class InwardLoanController extends Controller
{
    private $inwardLoanRepository;

    public function __construct(IInwardLoanRepositoryInterface $inwardLoanRepository)
    {
        $this->inwardLoanRepository = $inwardLoanRepository;
    }

    public function index()
    {
        return $this->inwardLoanRepository->index();
    }

    public function create()
    {
        return $this->inwardLoanRepository->create();
    }

    public function store(Request $request)
    {
        return $this->inwardLoanRepository->store($request);
    }

    public function show($id)
    {
        //
    }

    public function inward_loan_push($Id)
    {
        return $this->inwardLoanRepository->inward_loan_push($Id);
    }

    public function inward_loan_payment($Id)
    {
        return $this->inwardLoanRepository->inward_loan_payment($Id);
    }

    public function edit($id)
    {
        return $this->inwardLoanRepository->edit($id);
    }

    public function update(Request $request, $id)
    {
        return $this->inwardLoanRepository->update($request, $id);
    }

    public function inward_loan_save_payment(Request $request, $id)
    {
        return $this->inwardLoanRepository->inward_loan_save_payment($request, $id);
    }
}
