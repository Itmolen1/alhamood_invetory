<?php


namespace App\WebRepositories\Interfaces;


use App\Http\Requests\ProductRequest;
use Illuminate\Http\Request;

interface IProductRepositoryInterface
{


    public function index();

    public function create();

    public function store(ProductRequest $productRequest);

    public function update(Request $request, $Id);

    public function getById($Id);

    public function edit($Id);

    public function delete(Request $request, $Id);

    public function  restore($Id);

    public function trashed();

    public function productDetails($Id);

}
