<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentTermRequest;
use App\Models\PaymentTerm;
use App\WebRepositories\Interfaces\IPaymentTermRepositoryInterface;
use Illuminate\Http\Request;

class PaymentTermController extends Controller
{
    private $paymentTermRepository;
    public function __construct(IPaymentTermRepositoryInterface $paymentTermRepository)
    {
      $this->paymentTermRepository = $paymentTermRepository;
    }
    public function index()
    {
        return $this->paymentTermRepository->index();
    }


    public function create()
    {
        return $this->paymentTermRepository->create();
    }


    public function store(PaymentTermRequest $paymentTermRequest)
    {
        return $this->paymentTermRepository->store($paymentTermRequest);
    }


    public function show($Id)
    {

    }


    public function edit($Id)
    {
        return $this->paymentTermRepository->edit($Id);
    }


    public function update(Request $request, $Id)
    {
        return $this->paymentTermRepository->update($request, $Id);
    }


    public function destroy(Request  $request, $Id)
    {
        return $this->paymentTermRepository->delete($request, $Id);
    }
}
