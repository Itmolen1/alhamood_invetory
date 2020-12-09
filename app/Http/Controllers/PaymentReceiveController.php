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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PaymentReceive  $paymentReceive
     * @return \Illuminate\Http\Response
     */
    public function edit(PaymentReceive $paymentReceive)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PaymentReceive  $paymentReceive
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PaymentReceive $paymentReceive)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PaymentReceive  $paymentReceive
     * @return \Illuminate\Http\Response
     */
    public function destroy(PaymentReceive $paymentReceive)
    {
        //
    }

    public function customer_payments_push(Request $request, $Id)
    {
       return $this->paymentReceiveRepository->customer_payments_push($request, $Id);
    }
}
