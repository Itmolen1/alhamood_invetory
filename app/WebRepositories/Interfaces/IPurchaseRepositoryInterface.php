<?php


namespace App\WebRepositories\Interfaces;


use App\Http\Requests\PurchaseRequest;
use Illuminate\Http\Request;

interface IPurchaseRepositoryInterface
{

    public function index();

    public function create();

    public function store(PurchaseRequest $purchaseRequest);

    public function update(Request $request, $Id);

    public function getById($Id);

    public function edit($Id);

    public function delete(Request $request, $Id);

    public function  restore($Id);

    public function trashed();

    public function invoiceNumber();

}
