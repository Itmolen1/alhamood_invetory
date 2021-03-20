<?php

namespace App\Http\Controllers;

use App\Models\CustomerPrice;
use App\WebRepositories\Interfaces\ICustomerPricesRepositoryInterface;
use Illuminate\Http\Request;

class CustomerPriceController extends Controller
{
    private $customerPricesRepository;
    public function __construct(ICustomerPricesRepositoryInterface $customerPricesRepository){
        $this->customerPricesRepository = $customerPricesRepository;
    }
    public function index()
    {
        return $this->customerPricesRepository->index();
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        return $this->customerPricesRepository->store($request);
    }

    public function show(CustomerPrice $customerPrice)
    {
        //
    }

    public function edit(CustomerPrice $customerPrice)
    {
        //
    }

    public function update(Request $request, CustomerPrice $customerPrice)
    {
        //
    }

    public function destroy(CustomerPrice $customerPrice)
    {
        //
    }
}
