<?php


namespace App\WebRepositories;


use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\Unit;
use App\WebRepositories\Interfaces\IProductRepositoryInterface;
use Illuminate\Http\Request;

class ProductRepository implements IProductRepositoryInterface
{

    public function index()
    {
        // TODO: Implement index() method.
        $products = Product::with('user','company','unit')->get();
        return view('admin.product.index',compact('products'));
    }

    public function create()
    {
        // TODO: Implement create() method.
        $units = Unit::all();
        return view('admin.product.create',compact('units'));
    }

    public function store(ProductRequest $productRequest)
    {
        // TODO: Implement store() method.
        $user_id = session('user_id');
        $company_id = session('company_id');
        $data =
            [
                'Name' =>$productRequest->Name,
                'unit_id' => $productRequest->unit_id,
                'user_id' => $user_id,
                'company_id' => $company_id,
            ];
        Product::create($data);
        return redirect()->route('products.index')->with('success','Record Inserted Successfully');
    }

    public function update(Request $request, $Id)
    {
        // TODO: Implement update() method.
        $data = Product::find($Id);
        $user_id = session('user_id');
        $data->update([
            'Name' => $request->Name,
            'user_id' => $user_id,
            'unit_id' => $request->unit_id,
        ]);
        return redirect()->route('products.index')->with('update','Record Updated Successfully');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        // TODO: Implement edit() method.
        $units = Unit::all();
        $product = Product::with('unit')->find($Id);
        return view('admin.product.edit',compact('units','product'));
    }

    public function delete(Request $request, $Id)
    {
        // TODO: Implement delete() method.
        $data = Product::find($Id);
        $data->delete();
        return redirect()->route('products.index')->with('delete','Record Deleted Successfully');
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
    }

    public function productDetails($Id)
    {
        // TODO: Implement productDetails() method.
        $data = Product::with('unit')->find($Id);
        return response()->json($data);
    }   
}
