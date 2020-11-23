<?php


namespace App\WebRepositories;


use App\Http\Requests\CustomerRequest;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Region;
use App\WebRepositories\Interfaces\ICustomerRepositoryInterface;
use Illuminate\Http\Request;

class CustomerRepository implements ICustomerRepositoryInterface
{

    public function index()
    {
        // TODO: Implement index() method.
        $customers = Customer::with('company','user')->get();
        return view('admin.customer.index',compact('customers'));

    }

    public function create()
    {
        // TODO: Implement create() method.
        $regions = Region::with('city')->get();
        return view('admin.customer.create',compact('regions'));

    }

    public function store(CustomerRequest $customerRequest)
    {
        // TODO: Implement store() method.
        $user_id = session('user_id');
        $company_id = session('company_id');

        $filename = sprintf('thumbnail_%s.jpg',random_int(1,1000));
        if ($customerRequest->hasFile('fileUpload'))
            $filename = $customerRequest->file('fileUpload')->storeAs('customers', $filename,'public');

        else
            $filename = null;

        $customer = [
            'Name' =>$customerRequest->Name,
            'Mobile' =>$customerRequest->Mobile,
            'Representative' =>$customerRequest->Representative,
            'Phone' =>$customerRequest->Phone,
            'Address' =>$customerRequest->Address,
            'postCode' =>$customerRequest->postCode,
            'region_id' =>$customerRequest->region_id,
            'user_id' =>$user_id,
            'company_id' =>$company_id,
            'fileUpload' =>$filename,
            'Description' =>$customerRequest->Description,
            'registrationDate' =>$customerRequest->registrationDate,
            'TRNNumber' =>$customerRequest->TRNNumber,
            'companyType' =>$customerRequest->companyType,
            'paymentType' =>$customerRequest->paymentType,
            'paymentTerm' =>$customerRequest->paymentTerm,
        ];
        Customer::create($customer);
        return redirect()->route('customers.index');
    }

    public function update(Request $request, $Id)
    {
        // TODO: Implement update() method.

        $customer = Customer::find($Id);
        $filename = sprintf('thumbnail_%s.jpg',random_int(1,1000));
        if ($request->hasFile('fileUpload'))
            $filename = $request->file('fileUpload')->storeAs('customers', $filename,'public');

        else
            $filename = $customer->fileUpload;

        $user_id = session('user_id');
        $customer->update([
            'Name' =>$request->Name,
            'Mobile' =>$request->Mobile,
            'Representative' =>$request->Representative,
            'Phone' =>$request->Phone,
            'Address' =>$request->Address,
            'postCode' =>$request->postCode,
            'region_id' =>$request->region_id,
            'user_id' =>$user_id,
//            'company_id' =>$company_id,
            'fileUpload' =>$filename,
            'Description' =>$request->Description,
            'registrationDate' =>$request->registrationDate,
            'TRNNumber' =>$request->TRNNumber,
            'companyType' =>$request->companyType,
            'paymentType' =>$request->paymentType,
            'paymentTerm' =>$request->paymentTerm,

        ]);
        return redirect()->route('customers.index');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.


    }

    public function edit($Id)
    {
        // TODO: Implement edit() method.
        $regions = Region::with('city')->get();
        $customer = Customer::with('region')->find($Id);
        return view('admin.customer.edit',compact('customer','regions'));
    }

    public function delete(Request $request, $Id)
    {
        // TODO: Implement delete() method.
        $data = Customer::findOrFail($Id);
        $data->delete();
        return redirect()->route('customers.index');
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
    }


    public function getCustomerVehicleDetails($Id)
    {
        // TODO: Implement getCustomerVehicleDetails() method.
    }
}
