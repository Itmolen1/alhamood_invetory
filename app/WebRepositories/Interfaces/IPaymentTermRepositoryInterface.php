<?php


namespace App\WebRepositories\Interfaces;


use App\Http\Requests\PaymentTermRequest;
use App\Http\Requests\PaymentTypeRequest;
use Illuminate\Http\Request;

interface IPaymentTermRepositoryInterface
{
    public function index();

    public function create();

    public function store(PaymentTermRequest $paymentTermRequest);

    public function update(Request $request, $Id);

    public function getById($Id);

    public function edit($Id);

    public function delete(Request $request, $Id);

    public function  restore($Id);

    public function trashed();
}
