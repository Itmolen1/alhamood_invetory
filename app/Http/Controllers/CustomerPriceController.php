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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CustomerPrice  $customerPrice
     * @return \Illuminate\Http\Response
     */
    public function edit(CustomerPrice $customerPrice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CustomerPrice  $customerPrice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CustomerPrice $customerPrice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CustomerPrice  $customerPrice
     * @return \Illuminate\Http\Response
     */
    public function destroy(CustomerPrice $customerPrice)
    {
        //
    }
}
