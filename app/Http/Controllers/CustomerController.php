<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use App\WebRepositories\Interfaces\ICustomerRepositoryInterface;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    private $customerRepository;

    public function __construct(ICustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function index()
    {
        return $this->customerRepository->index();
    }

    public function create()
    {
        return $this->customerRepository->create();
    }

    public function store(CustomerRequest $customerRequest)
    {
        return $this->customerRepository->store($customerRequest);
    }

    public function show(Customer $customer)
    {
        //
    }

    public function edit($Id)
    {
        return $this->customerRepository->edit($Id);
    }

    public function update(Request $request, $Id)
    {
        return $this->customerRepository->update($request, $Id);
    }

    public function destroy(Request $request, $Id)
    {
        return $this->customerRepository->delete($request, $Id);
    }

    public function customerDetails($id)
    {
        return $this->customerRepository->customerDetails($id);
    }
}
