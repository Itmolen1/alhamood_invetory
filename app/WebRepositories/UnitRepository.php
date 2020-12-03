<?php


namespace App\WebRepositories;


use App\Http\Requests\UnitRequest;
use App\Models\Unit;
use App\Models\Product;
use App\WebRepositories\Interfaces\IUnitRepositoryInterface;
use Illuminate\Http\Request;

class UnitRepository implements IUnitRepositoryInterface
{

    public function index()
    {
        // TODO: Implement index() method.
        $units = Unit::with('user','company','product')->get();
        return view('admin.unit.index',compact('units'));
    }

    public function create()
    {
        // TODO: Implement create() method.
        $products = Product::all();
        return view('admin.unit.create',compact('products'));
    }

    public function store(UnitRequest $unitRequest)
    {
        // TODO: Implement store() method.
        $user_id = session('user_id');
        $company_id = session('company_id');
        $unit = [
            'Name' => $unitRequest->Name,
            'product_id' => $unitRequest->product_id,
            'user_id' => $user_id,
            'company_id' => $company_id,
        ];
        Unit::create($unit);
        return redirect()->route('units.index')->with('success','Record Inserted Successfully');
    }

    public function update(Request $request, $Id)
    {
        // TODO: Implement update() method.
        $unit = Unit::find($Id);
        $user_id = session('user_id');
        $unit->update([
            'Name' => $request->Name,
            'product_id' => $request->product_id,
            'user_id' => $user_id,
        ]);
        return redirect()->route('units.index')->with('update','Record Updated Successfully');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        // TODO: Implement edit() method.
        $products = Product::all();
        $unit = Unit::with('product')->find($Id);
        return view('admin.unit.edit',compact('unit','products'));
    }

    public function delete(Request $request, $Id)
    {
        // TODO: Implement delete() method.
        $data = Unit::findOrFail($Id);
        $data->delete();
        return redirect()->route('units.index')->with('delete','Record Deleted Successfully');

    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
    }
}
