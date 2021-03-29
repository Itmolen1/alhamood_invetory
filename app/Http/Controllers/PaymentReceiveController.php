<?php

namespace App\Http\Controllers;

use App\Models\PaymentReceive;
use App\WebRepositories\Interfaces\IPaymentReceiveRepositoryInterface;
use Illuminate\Http\Request;

class PaymentReceiveController extends Controller
{
    private $paymentReceiveRepository;
    public function __construct(IPaymentReceiveRepositoryInterface  $paymentReceiveRepository){
        $this->paymentReceiveRepository = $paymentReceiveRepository;
    }
    public function index()
    {
        return $this->paymentReceiveRepository->index();
    }

    public function create()
    {
        return $this->paymentReceiveRepository->create();
    }

    public function store(Request $request)
    {
        return $this->paymentReceiveRepository->store($request);
    }

    public function show($Id)
    {
        return $this->paymentReceiveRepository->getById($Id);
    }

    public function getCustomerPaymentDetail($Id)
    {
        return $this->paymentReceiveRepository->getCustomerPaymentDetail($Id);
    }

    public function edit($Id)
    {
        return $this->paymentReceiveRepository->edit($Id);
    }

    public function payment_receivesUpdate(Request $request)
    {
        $Id=$request->Id;
        return $this->paymentReceiveRepository->update($request, $Id);
    }

    public function destroy(PaymentReceive $paymentReceive)
    {
        //
    }

    public function customer_payments_push(Request $request, $Id)
    {
       return $this->paymentReceiveRepository->customer_payments_push($request, $Id);
    }
}
