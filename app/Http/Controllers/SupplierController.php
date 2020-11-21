<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use App\WebRepositories\Interfaces\ISupplierRepositoryInterface;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * @var ISupplierRepositoryInterface
     */
    private $supplierRepository;

    public function __construct(ISupplierRepositoryInterface $supplierRepository)
    {
        $this->supplierRepository = $supplierRepository;
    }

    public function index()
    {
        return $this->supplierRepository->index();
    }


    public function create()
    {
        return $this->supplierRepository->create();
    }


    public function store(SupplierRequest $supplierRequest)
    {
        return $this->supplierRepository->store($supplierRequest);
    }


    public function show($Id)
    {
        //
    }


    public function edit($Id)
    {
        return $this->supplierRepository->edit($Id);
    }


    public function update(Request $request, $Id)
    {
        return $this->supplierRepository->update($request, $Id);
    }

    public function destroy(Request $request, $Id)
    {
        return $this->supplierRepository->delete($request, $Id);
    }

    public function supplierDetails($Id)
    {
        return $this->supplierRepository->supplierDetails($Id);
    }
}
