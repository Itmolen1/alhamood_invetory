<?php


namespace App\WebRepositories\Interfaces;


use App\Http\Requests\CustomerRequest;
use Illuminate\Http\Request;

interface ICustomerRepositoryInterface
{
    public function index();

    public function create();

    public function store(CustomerRequest $customerRequest);

    public function update(Request $request, $Id);

    public function getById($Id);

    public function edit($Id);

    public function delete(Request $request, $Id);

    public function restore($Id);

    public function trashed();

    public function getCustomerVehicleDetails($Id);
}
