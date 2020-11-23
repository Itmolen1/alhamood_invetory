<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\IProductRepositoryInterface;
use App\Http\Controllers\Controller;
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

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        try
        {
            $product = Product::create($request->all());
            return $this->userResponse->Success($product);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
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

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        try
        {
            $product = Product::find($id);
            if(is_null($product))
            {
                return $this->userResponse->Failed($product = (object)[],'Not Found.');
            }
            $product->update($request->all());
            $product->save();
            return $this->userResponse->Success($product);
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
}
