<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierAdvanceRequest;
use App\Models\SupplierAdvance;
use App\WebRepositories\Interfaces\ISupplierAdvanceRepositoryInterface;
use Illuminate\Http\Request;

class SupplierAdvanceController extends Controller
{
    private $supplierAdvanceRepository;

    public function __construct(ISupplierAdvanceRepositoryInterface $supplierAdvanceRepository)
    {
        $this->supplierAdvanceRepository = $supplierAdvanceRepository;
    }

    public function index()
    {
        return $this->supplierAdvanceRepository->index();
    }

    public function create()
    {
        return $this->supplierAdvanceRepository->create();
    }

    public function store(SupplierAdvanceRequest $supplierAdvanceRequest)
    {
        return $this->supplierAdvanceRepository->store($supplierAdvanceRequest);
    }

    public function show($Id)
    {
        return $this->supplierAdvanceRepository->getById($Id);
    }

    public function edit($Id)
    {
        return $this->supplierAdvanceRepository->edit($Id);
    }

    public function update(Request $request, $Id)
    {
        return $this->supplierAdvanceRepository->update($request, $Id);
    }

    public function destroy(Request $request, $Id)
    {
        return $this->supplierAdvanceRepository->delete($request, $Id);
    }

    public function supplier_advances_push(Request $request, $Id)
    {
        return $this->supplierAdvanceRepository->supplier_advances_push($request, $Id);
    }

    public function supplier_advances_get_disburse($Id)
    {
        return $this->supplierAdvanceRepository->supplier_advances_get_disburse($Id);
    }

    public function supplier_advances_save_disburse(Request $request)
    {
        return $this->supplierAdvanceRepository->supplier_advances_save_disburse($request);
    }
}
