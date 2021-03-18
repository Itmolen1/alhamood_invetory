<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\WebRepositories\Interfaces\IOutwardLoandRepositoryInterface;
use Illuminate\Http\Request;

class OutwardLoanController extends Controller
{
    private $outwardLoanRepository;

    public function __construct(IOutwardLoandRepositoryInterface $outwardLoanRepository)
    {
        $this->outwardLoanRepository = $outwardLoanRepository;
    }

    public function index()
    {
        return $this->outwardLoanRepository->index();
    }

    public function create()
    {
        return $this->outwardLoanRepository->create();
    }

    public function store(Request $request)
    {
        return $this->outwardLoanRepository->store($request);
    }

    public function show($id)
    {
        //
    }

    public function outward_loan_push($Id)
    {
        return $this->outwardLoanRepository->outward_loan_push($Id);
    }

    public function outward_loan_payment($Id)
    {
        return $this->outwardLoanRepository->outward_loan_payment($Id);
    }

    public function edit($id)
    {
        return $this->outwardLoanRepository->edit($id);
    }

    public function update(Request $request, $id)
    {
        return $this->outwardLoanRepository->update($request, $id);
    }

    public function outward_loan_save_payment(Request $request, $id)
    {
        return $this->outwardLoanRepository->outward_loan_save_payment($request, $id);
    }
}
