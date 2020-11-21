<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\WebRepositories\Interfaces\IProductRepositoryInterface;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * @var IProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(IProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function index()
    {
        return $this->productRepository->index();
    }


    public function create()
    {
        return $this->productRepository->create();
    }


    public function store(ProductRequest $productRequest)
    {
        return $this->productRepository->store($productRequest);
    }


    public function show($Id)
    {
        //
    }


    public function edit($Id)
    {
        return $this->productRepository->edit($Id);
    }


    public function update(Request $request, $Id)
    {
        return $this->productRepository->update($request, $Id);
    }

    public function destroy(Request $request, $Id)
    {
        return $this->productRepository->delete($request, $Id);
    }

    public function productDetails($Id)
    {
        return $this->productRepository->productDetails($Id);
    }
}
