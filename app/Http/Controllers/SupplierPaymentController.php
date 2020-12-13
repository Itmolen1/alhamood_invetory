<?php

namespace App\Http\Controllers;

use App\Models\SupplierPayment;
use App\WebRepositories\Interfaces\ISupplierPaymentRepositoryInterface;
use Illuminate\Http\Request;

class SupplierPaymentController extends Controller
{
    private $supplierPaymentRepository;
   public function __construct(ISupplierPaymentRepositoryInterface $supplierPaymentRepository){
    $this->supplierPaymentRepository = $supplierPaymentRepository;
   }
    public function index()
    {
        return $this->supplierPaymentRepository->index();
    }


    public function create()
    {
        return $this->supplierPaymentRepository->create();
    }

    public function store(Request $request)
    {
        return $this->supplierPaymentRepository->store($request);
    }


    public function show($Id)
    {
        return $this->supplierPaymentRepository->getById($Id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SupplierPayment  $supplierPayment
     * @return \Illuminate\Http\Response
     */
    public function edit(SupplierPayment $supplierPayment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SupplierPayment  $supplierPayment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SupplierPayment $supplierPayment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SupplierPayment  $supplierPayment
     * @return \Illuminate\Http\Response
     */
    public function destroy(SupplierPayment $supplierPayment)
    {
        //
    }

    public function supplier_payments_push(Request $request, $Id)
    {
        return $this->supplierPaymentRepository->supplier_payments_push($request, $Id);
    }

}
