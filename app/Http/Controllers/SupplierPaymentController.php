<?php

namespace App\Http\Controllers;

use App\Models\SupplierPayment;
use App\WebRepositories\Interfaces\ISupplierPaymentRepositoryInterface;
use Illuminate\Http\Request;

class SupplierPaymentController extends Controller
{
    private $supplierPaymentRepository;
    public function __construct(ISupplierPaymentRepositoryInterface $supplierPaymentRepository)
    {
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

    public function getSupplierPaymentDetail($Id)
    {
        return $this->supplierPaymentRepository->getSupplierPaymentDetail($Id);
    }

    public function edit($Id)
    {
        return $this->supplierPaymentRepository->edit($Id);
    }

    public function update(Request $request, $Id)
    {
        return $this->supplierPaymentRepository->update($request, $Id);
    }

    public function destroy(SupplierPayment $supplierPayment)
    {
        //
    }

    public function supplier_payments_push(Request $request, $Id)
    {
        return $this->supplierPaymentRepository->supplier_payments_push($request, $Id);
    }

}
