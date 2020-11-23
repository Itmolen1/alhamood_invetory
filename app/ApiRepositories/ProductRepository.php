<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\IProductRepositoryInterface;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

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

    public function insert(Request $request)
    {
        $product = new Product();
        $product->Name=$request->Name;
        $product->Description=$request->Description;
        $product->company_id=$request->company_id;
        $product->createdDate=date('Y-m-d h:i:s');
        $product->isActive=1;
        $product->user_id = 1;//login user id
        $product->save();
        return new ProductResource(Product::find($product->Id));
    }

    public function update(ProductRequest $productRequest, $Id)
    {
        $product = Product::find($Id);
        $product->update($productRequest->all());
        return new ProductResource(Product::find($Id));
    }

    public function getById($Id)
    {
        return new ProductResource(Product::find($Id));
    }

    public function delete(Request $request, $Id)
    {
        $update = Product::find($Id);
        $update->update($request->all());
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
}
