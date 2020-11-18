<?php


namespace App\WebRepositories\Interfaces;


use App\Http\Requests\CustomerAdvanceRequest;
use Illuminate\Http\Request;

interface ICustomerAdvanceRepositoryInterface
{
    public function index();

    public function create();

    public function store(CustomerAdvanceRequest $customerAdvanceRequest);

    public function update(Request $request, $Id);

    public function getById($Id);

    public function edit($Id);

    public function delete(Request $request, $Id);

    public function  restore($Id);

    public function trashed();
}
