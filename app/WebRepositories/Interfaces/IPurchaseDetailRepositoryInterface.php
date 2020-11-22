<?php


namespace App\WebRepositories\Interfaces;


use App\Http\Requests\PurchaseDetailRequest;
use App\Http\Requests\PurchaseRequest;
use Illuminate\Http\Request;

interface IPurchaseDetailRepositoryInterface
{

    public function index();

    public function create();

    public function store(PurchaseDetailRequest $purchaseDetailRequest);

    public function update(Request $request, $Id);

    public function getById($Id);

    public function edit($Id);

    public function delete(Request $request, $Id);

    public function  restore($Id);

    public function trashed();

}
