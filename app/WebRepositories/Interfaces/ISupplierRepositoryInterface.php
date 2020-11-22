<?php


namespace App\WebRepositories\Interfaces;


use App\Http\Requests\SupplierRequest;
use Illuminate\Http\Request;

interface ISupplierRepositoryInterface
{

    public function index();

    public function create();

    public function store(SupplierRequest $supplierRequest);

    public function update(Request $request, $Id);

    public function getById($Id);

    public function edit($Id);

    public function delete(Request $request, $Id);

    public function  restore($Id);

    public function trashed();

    public function supplierDetails($Id);

}
