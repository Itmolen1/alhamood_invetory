<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseRequest;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\UpdateNote;
use App\WebRepositories\Interfaces\IPurchaseRepositoryInterface;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    /**
     * @var IPurchaseRepositoryInterface
     */
    private $purchaseRepository;

    public function __construct(IPurchaseRepositoryInterface $purchaseRepository)
    {
        $this->purchaseRepository = $purchaseRepository;
    }

    public function index()
    {
        return $this->purchaseRepository->index();
    }


    public function create()
    {
        return $this->purchaseRepository->create();
    }

    public function store(PurchaseRequest $purchaseRequest)
    {
        return $this->purchaseRepository->store($purchaseRequest);
    }


    public function show(Purchase $purchase)
    {
        //
    }


    public function edit($Id)
    {
        return $this->purchaseRepository->edit($Id);
    }

    public function print($id)
    {
        return $this->purchaseRepository->print($id);
    }

    public function purchaseUpdate(Request $request, $Id)
    {
        return $this->purchaseRepository->update($request, $Id);
    }


    public function update(Request $request, Purchase $purchase)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function destroy(Purchase $purchase)
    {
        //
    }

    public function supplierSaleDetails($Id)
    {
        return $this->purchaseRepository->supplierSaleDetails($Id);
    }
}
