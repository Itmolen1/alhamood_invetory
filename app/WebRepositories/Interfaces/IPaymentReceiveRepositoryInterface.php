<?php


namespace App\WebRepositories\Interfaces;


use Illuminate\Http\Request;

interface IPaymentReceiveRepositoryInterface
{

    public function index();

    public function create();

    public function store(Request $request);

    public function update(Request $request, $Id);

    public function getById($Id);

    public function edit($Id);

    public function delete(Request $request, $Id);

    public function  restore($Id);

    public function trashed();

    public function customer_payments_push(Request $request, $Id);

}
