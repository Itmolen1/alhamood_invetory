<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerAdvanceRequest;
use App\Models\CustomerAdvance;
use App\WebRepositories\Interfaces\ICustomerAdvanceRepositoryInterface;
use Illuminate\Http\Request;

class CustomerAdvanceController extends Controller
{
    private $customerAdvanceRepository;

    public function __construct(ICustomerAdvanceRepositoryInterface $customerAdvanceRepository)
    {
        $this->customerAdvanceRepository = $customerAdvanceRepository;
    }

    public function index()
    {
        return $this->customerAdvanceRepository->index();
    }

    public function create()
    {
        return $this->customerAdvanceRepository->create();
    }

    public function store(CustomerAdvanceRequest $customerAdvanceRequest)
    {
        return $this->customerAdvanceRepository->store($customerAdvanceRequest);
    }

    public function show($Id)
    {
        return $this->customerAdvanceRepository->getById($Id);
    }

    public function edit($Id)
    {
        return $this->customerAdvanceRepository->edit($Id);
    }

    public function update(Request $request, $Id)
    {
        return $this->customerAdvanceRepository->update($request, $Id);
    }

    public function destroy(Request $request, $Id)
    {
        return $this->customerAdvanceRepository->delete($request, $Id);
    }

    public function customer_advances_push(Request $request,$Id)
    {
        return $this->customerAdvanceRepository->customer_advances_push($request, $Id);
    }

    public function customer_advances_get_disburse($Id)
    {
        return $this->customerAdvanceRepository->customer_advances_get_disburse($Id);
    }

    public function customer_advances_save_disburse(Request $request)
    {
        return $this->customerAdvanceRepository->customer_advances_save_disburse($request);
    }
}
