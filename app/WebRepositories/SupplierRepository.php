<?php


namespace App\WebRepositories;


use App\Http\Requests\SupplierRequest;
use App\Models\Region;
use App\Models\Supplier;
use App\WebRepositories\Interfaces\ISupplierRepositoryInterface;
use Illuminate\Http\Request;

class SupplierRepository implements ISupplierRepositoryInterface
{

    public function index()
    {
        // TODO: Implement index() method.
        $suppliers = Supplier::with('company','user')->get();
        return view('admin.supplier.index',compact('suppliers'));
    }

    public function create()
    {
        // TODO: Implement create() method.
        $regions = Region::with('city')->get();
        return view('admin.supplier.create',compact('regions'));
    }

    public function store(SupplierRequest $supplierRequest)
    {
        // TODO: Implement store() method.
        $user_id = session('user_id');
        $company_id = session('company_id');

        $filename = sprintf('thumbnail_%s.jpg',random_int(1,1000));
        if ($supplierRequest->hasFile('fileUpload'))
            $filename = $supplierRequest->file('fileUpload')->storeAs('suppliers', $filename,'public');

        else
            $filename = null;

        $supplier = [
            'Name' =>$supplierRequest->Name,
            'Mobile' =>$supplierRequest->Mobile,
            'Representative' =>$supplierRequest->Representative,
            'Phone' =>$supplierRequest->Phone,
            'Address' =>$supplierRequest->Address,
            'postCode' =>$supplierRequest->postCode,
            'region_id' =>$supplierRequest->region_id,
            'Email' =>$supplierRequest->Email,
            'user_id' =>$user_id,
            'company_id' =>$company_id,
            'fileUpload' =>$filename,
            'Description' =>$supplierRequest->Description,
            'registrationDate' =>$supplierRequest->registrationDate,
            'TRNNumber' =>$supplierRequest->TRNNumber,
            'companyType' =>$supplierRequest->companyType,
            'paymentType' =>$supplierRequest->paymentType,
            'paymentTerm' =>$supplierRequest->paymentTerm,
        ];
        Supplier::create($supplier);
        return redirect()->route('suppliers.index');
    }

    public function update(Request $request, $Id)
    {
        // TODO: Implement update() method.
        $supplier = Supplier::find($Id);
        $filename = sprintf('thumbnail_%s.jpg',random_int(1,1000));
        if ($request->hasFile('fileUpload'))
            $filename = $request->file('fileUpload')->storeAs('suppliers', $filename,'public');

        else
            $filename = $supplier->fileUpload;

        $user_id = session('user_id');
        $supplier->update([
            'Name' =>$request->Name,
            'Mobile' =>$request->Mobile,
            'Representative' =>$request->Representative,
            'Phone' =>$request->Phone,
            'Address' =>$request->Address,
            'postCode' =>$request->postCode,
            'region_id' =>$request->region_id,
            'user_id' =>$user_id,
            'Email' =>$request->Email,
//            'company_id' =>$company_id,
            'fileUpload' =>$filename,
            'Description' =>$request->Description,
            'registrationDate' =>$request->registrationDate,
            'TRNNumber' =>$request->TRNNumber,
            'companyType' =>$request->companyType,
            'paymentType' =>$request->paymentType,
            'paymentTerm' =>$request->paymentTerm,

        ]);
        return redirect()->route('suppliers.index');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        // TODO: Implement edit() method.
        $regions = Region::with('city')->get();
        $supplier = Supplier::with('region')->find($Id);
        return view('admin.supplier.edit',compact('supplier','regions'));
    }

    public function delete(Request $request, $Id)
    {
        // TODO: Implement delete() method.
        $data = Supplier::findOrFail($Id);
        $data->delete();
        return redirect()->route('suppliers.index');
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
    }

    public function supplierDetails($Id)
    {
        // TODO: Implement supplierDetails() method.
        $suppliers = Supplier::find($Id);
        return response()->json($suppliers);
    }
}
