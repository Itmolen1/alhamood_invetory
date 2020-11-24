<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\ICustomerRepositoryInterface;
use App\Http\Requests\CustomerRequest;
use App\Http\Resources\Customer\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerRepository implements ICustomerRepositoryInterface
{
    public function all()
    {
        return CustomerResource::collection(Customer::all()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return CustomerResource::Collection(Customer::all()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(Request $request)
    {
        $userId = Auth::id();
        $customer = new Customer();
        $customer->Name=$request->Name;
        $customer->Representative=$request->Representative;
        $customer->companyType=$request->companyType;
        $customer->paymentType=$request->paymentType;
        $customer->paymentTerm=$request->paymentTerm;
        $customer->TRNNumber=$request->TRNNumber;
        $customer->fileUpload=$request->fileUpload;
        $customer->Phone=$request->Phone;
        $customer->Mobile=$request->Mobile;
        $customer->Address=$request->Address;
        $customer->postCode=$request->postCode;
        $customer->registrationDate=$request->registrationDate;
        $customer->Description=$request->Description;
        $customer->company_id=$request->company_id;
        $customer->region_id=$request->region_id;
        $customer->createdDate=date('Y-m-d h:i:s');
        $customer->isActive=1;
        $customer->user_id = $userId ?? 0;
        $customer->save();
        return new CustomerResource(Customer::find($customer->id));
    }

    public function update(CustomerRequest $customerRequest, $Id)
    {
        $userId = Auth::id();
        $customer = Customer::find($Id);
        $customerRequest['user_id']=$userId ?? 0;
        $customer->update($customerRequest->all());
        return new CustomerResource(Customer::find($Id));
    }

    public function getById($Id)
    {
        return new CustomerResource(Customer::find($Id));
    }

    public function delete(Request $request, $Id)
    {
        $userId = Auth::id();
        $request['user_id']=$userId ?? 0;
        $update = Customer::find($Id);
        $update->user_id=$userId;
        $update->save();
        $customer = Customer::withoutTrashed()->find($Id);
        if($customer->trashed())
        {
            return new CustomerResource(Customer::onlyTrashed()->find($Id));
        }
        else
        {
            $customer->delete();
            return new CustomerResource(Customer::onlyTrashed()->find($Id));
        }
    }

    public function restore($Id)
    {
        $customer = Customer::onlyTrashed()->find($Id);
        if (!is_null($customer))
        {
            $customer->restore();
            return new CustomerResource(Customer::find($Id));
        }
        return new CustomerResource(Customer::find($Id));
    }

    public function trashed()
    {
        $customer = Customer::onlyTrashed()->get();
        return CustomerResource::collection($customer);
    }
}
