<?php

namespace App\Http\Controllers;

use App\Models\PaymentType;
use App\Http\Requests\PaymentTypeRequest;
use App\WebRepositories\Interfaces\IPaymentTypeRepositoryInterface;
use Illuminate\Http\Request;

class PaymentTypeController extends Controller
{
    private $paymentTypeRepository;
   public function __construct(IPaymentTypeRepositoryInterface $paymentTypeRepository)
   {
       $this->paymentTypeRepository = $paymentTypeRepository;
   }
    public function index()
    {
        return $this->paymentTypeRepository->index();
    }

  
    public function create()
    {
        return $this->paymentTypeRepository->create();
    }

    
    public function store(PaymentTypeRequest $paymentTypeRequest)
    {
        return $this->paymentTypeRepository->store($paymentTypeRequest);
    }

  
    public function show($Id)
    {
        //
    }

   
    public function edit($Id)
    {
        return $this->paymentTypeRepository->edit($Id);
    }

   
    public function update(Request $request, $Id)
    {
        return $this->paymentTypeRepository->update($request, $Id);
    }

    
    public function destroy(Request $request, $Id)
    {
         return $this->paymentTypeRepository->delete($request, $Id);
    }
}
