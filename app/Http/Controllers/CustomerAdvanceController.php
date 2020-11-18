<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerAdvanceRequest;
use App\Models\CustomerAdvance;
use App\WebRepositories\Interfaces\ICustomerAdvanceRepositoryInterface;
use Illuminate\Http\Request;

class CustomerAdvanceController extends Controller
{
    /**
     * @var ICustomerAdvanceRepositoryInterface
     */
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


    public function show(CustomerAdvance $customerAdvance)
    {
        //
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
}
