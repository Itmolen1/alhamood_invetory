<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\IProductRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\MISC\ServiceResponse;
use App\Models\Product;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;

class ProductController extends Controller
{
    private $productRepository;
    private $userResponse;

    public function __construct(ServiceResponse $serviceResponse, IProductRepositoryInterface $productRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->productRepository=$productRepository;
    }

    public function index()
    {
        try
        {
            return $this->userResponse->Success($this->productRepository->all());
        }
        catch (Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function paginate($page_no,$page_size)
    {
        try
        {
            return $this->userResponse->Success($this->productRepository->paginate($page_no,$page_size));
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function store(Request $request)
    {
        return $this->productRepository->insert($request);
    }

    public function show($id)
    {
        try
        {
            $product = Product::find($id);
            if(is_null($product))
            {
                return $this->userResponse->Failed($product = (object)[],'Not Found.');
            }
            return $this->userResponse->Success($product);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function update(ProductRequest $productRequest, $id)
    {
        try
        {
            $product = Product::find($id);
            if(is_null($product))
            {
                return $this->userResponse->Failed($product = (object)[],'Not Found.');
            }
            return $this->productRepository->update($productRequest,$id);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function destroy(Request $request,$Id)
    {
        try
        {
            $product = Product::find($Id);
            if(is_null($product))
            {
                return $this->userResponse->Failed($product = (object)[],'Not Found.');
            }
            $product = $this->productRepository->delete($request,$Id);
            return $this->userResponse->Success($product);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function restore($Id)
    {
        try {
            $restore = Product::withTrashed()->where('Id', $Id)->restore();
            return $this->userResponse->Success($restore);

        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public  function  trash()
    {
        $trashed = $this->productRepository->trashed();
        return $this->userResponse->Success($trashed);
    }

    public function ActivateDeactivate($Id)
    {
        try
        {
            $product = Product::find($Id);
            if(is_null($product))
            {
                return $this->userResponse->Failed($product = (object)[],'Not Found.');
            }
            $result=$this->productRepository->ActivateDeactivate($Id);
            return $this->userResponse->Success($result);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }
}
