<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\ISupplierRepositoryInterface;
use App\Http\Requests\SupplierRequest;
use App\Http\Resources\Supplier\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $userId = Auth::id();
        $supplier = new Supplier();
        $supplier->Name=$request->Name;
        $supplier->Representative=$request->Representative;
        $supplier->company_type_id=$request->company_type_id;
        $supplier->payment_type_id=$request->payment_type_id;
        $supplier->payment_term_id=$request->payment_term_id;
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
        $supplier->user_id = $userId ?? 0;
        $supplier->save();
        return new SupplierResource(Supplier::find($supplier->id));
    }

    public function update(SupplierRequest $supplierRequest, $Id)
    {
        $userId = Auth::id();
        $supplier = Supplier::find($Id);
        $supplierRequest['user_id']=$userId ?? 0;
        $supplier->update($supplierRequest->all());
        return new SupplierResource(Supplier::find($Id));
    }

    public function getById($Id)
    {
        return new SupplierResource(Supplier::find($Id));
    }

    public function delete(Request $request, $Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = Supplier::find($Id);
        $update->user_id=$userId;
        $update->save();
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

    public function ActivateDeactivate($Id)
    {
        $supplier = Supplier::find($Id);
        if($supplier->isActive==1)
        {
            $supplier->isActive=0;
        }
        else
        {
            $supplier->isActive=1;
        }
        $supplier->update();
        return new SupplierResource(Supplier::find($Id));
    }
}
