<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\ISupplierRepositoryInterface;
use App\Http\Requests\SupplierRequest;
use App\Http\Resources\Supplier\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierRepository implements ISupplierRepositoryInterface
{
    public function all()
    {
        return SupplierResource::collection(Supplier::all()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return SupplierResource::Collection(Supplier::all()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(Request $request)
    {
        $supplier = new Supplier();
        $supplier->Name=$request->Name;
        $supplier->Representative=$request->Representative;
        $supplier->companyType=$request->companyType;
        $supplier->paymentType=$request->paymentType;
        $supplier->paymentTerm=$request->paymentTerm;
        $supplier->TRNNumber=$request->TRNNumber;
        $supplier->fileUpload=$request->fileUpload;
        $supplier->Phone=$request->Phone;
        $supplier->Mobile=$request->Mobile;
        $supplier->Address=$request->Address;
        $supplier->postCode=$request->postCode;
        $supplier->registrationDate=$request->registrationDate;
        $supplier->Description=$request->Description;
        $supplier->company_id=$request->company_id;
        $supplier->region_id=$request->region_id;
        $supplier->createdDate=date('Y-m-d h:i:s');
        $supplier->isActive=1;
        $supplier->user_id = 1;//login user id
        $supplier->save();
        return new SupplierResource(Supplier::find($supplier->id));
    }

    public function update(SupplierRequest $supplierRequest, $Id)
    {
        $supplier = Supplier::find($Id);
        $supplier->update($supplierRequest->all());
        return new SupplierResource(Supplier::find($Id));
    }

    public function getById($Id)
    {
        return new SupplierResource(Supplier::find($Id));
    }

    public function delete(Request $request, $Id)
    {
        $update = Supplier::find($Id);
        $update->update($request->all());
        $supplier = Supplier::withoutTrashed()->find($Id);
        if($supplier->trashed())
        {
            return new SupplierResource(Supplier::onlyTrashed()->find($Id));
        }
        else
        {
            $supplier->delete();
            return new SupplierResource(Supplier::onlyTrashed()->find($Id));
        }
    }

    public function restore($Id)
    {
        $supplier = Supplier::onlyTrashed()->find($Id);
        if (!is_null($supplier))
        {
            $supplier->restore();
            return new SupplierResource(Supplier::find($Id));
        }
        return new SupplierResource(Supplier::find($Id));
    }

    public function trashed()
    {
        $supplier = Supplier::onlyTrashed()->get();
        return SupplierResource::collection($supplier);
    }
}
