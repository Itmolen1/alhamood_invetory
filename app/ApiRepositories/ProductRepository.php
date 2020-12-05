<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\IProductRepositoryInterface;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductRepository implements IProductRepositoryInterface
{
    public function all()
    {
        return ProductResource::collection(Product::all()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return ProductResource::Collection(Product::all()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(ProductRequest $productRequest)
    {
        $userId = Auth::id();
        $product = new Product();
        $product->Name=$productRequest->Name;
        $product->Description=$productRequest->Description;
        $product->company_id=$productRequest->company_id;
        $product->unit_id=$productRequest->unit_id;
        $product->createdDate=date('Y-m-d h:i:s');
        $product->isActive=1;
        $product->user_id = $userId ?? 0;
        $product->save();
        return new ProductResource(Product::find($product->id));
    }

    public function update(Request $request, $Id)
    {
        $userId = Auth::id();
        $product = Product::find($Id);
        $request['user_id']=$userId ?? 0;
        $product->update($request->all());
        return new ProductResource(Product::find($Id));
    }

    public function getById($Id)
    {
        return new ProductResource(Product::find($Id));
    }

    public function delete(Request $request, $Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = Product::find($Id);
        $update->user_id=$userId;
        $update->save();
        $product = Product::withoutTrashed()->find($Id);
        if($product->trashed())
        {
            return new ProductResource(Product::onlyTrashed()->find($Id));
        }
        else
        {
            $product->delete();
            return new ProductResource(Product::onlyTrashed()->find($Id));
        }
    }

    public function restore($Id)
    {
        $product = Product::onlyTrashed()->find($Id);
        if (!is_null($product))
        {
            $product->restore();
            return new ProductResource(Product::find($Id));
        }
        return new ProductResource(Product::find($Id));
    }

    public function trashed()
    {
        $product = Product::onlyTrashed()->get();
        return ProductResource::collection($product);
    }

    public function ActivateDeactivate($Id)
    {
        $product = Product::find($Id);
        if($product->isActive==1)
        {
            $product->isActive=0;
        }
        else
        {
            $product->isActive=1;
        }
        $product->update();
        return new ProductResource(Product::find($Id));
    }
}
