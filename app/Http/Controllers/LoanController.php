<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\WebRepositories\Interfaces\ILoanRepositoryInterface;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    private $loanRepository;
    public function __construct(ILoanRepositoryInterface $loanRepository){
        $this->loanRepository = $loanRepository;
    }
    public function index()
    {
        return $this->loanRepository->index();
    }


    public function create()
    {
        return $this->loanRepository->create();
    }


    public function store(Request $request)
    {
        //dd($request->all());
        return $this->loanRepository->store($request);
    }

    
    public function show($Id)
    {
        //
    }

   
    public function edit($Id)
    {
        return $this->loanRepository->edit($Id);
    }

    
    public function update(Request $request, $Id)
    {
        return $this->loanRepository->update($request, $Id);
    }

    public function destroy(Request $request, $Id)
    {
        return $this->loanRepository->delete($request, $Id);
    }

    public function customerRemaining($Id)
    {
        return $this->loanRepository->customerRemaining($Id);
    }

    public function employeeRemaining($Id)
    {
        return $this->loanRepository->employeeRemaining($Id);
    }
}
