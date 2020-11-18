<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierAdvanceRequest;
use App\Models\SupplierAdvance;
use App\WebRepositories\Interfaces\ISupplierAdvanceRepositoryInterface;
use Illuminate\Http\Request;

class SupplierAdvanceController extends Controller
{
    /**
     * @var ISupplierAdvanceRepositoryInterface
     */
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
}
