<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\ICustomerRepositoryInterface;
use App\Http\Requests\CustomerRequest;
use App\Http\Resources\Customer\CustomerResource;
use App\Models\AccountTransaction;
use App\Models\CompanyType;
use App\Models\Customer;
use App\Models\PaymentTerm;
use App\Models\PaymentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function insert(CustomerRequest $customerRequest)
    {
        $userId = Auth::id();
        $customer = new Customer();
        $customer->Name=$customerRequest->Name;
        $customer->Representative=$customerRequest->Representative;
        $customer->company_type_id=$customerRequest->company_type_id;
        $customer->payment_type_id=$customerRequest->payment_type_id;
        $customer->payment_term_id=$customerRequest->payment_term_id;
        $customer->TRNNumber=$customerRequest->TRNNumber;
        $customer->fileUpload=$customerRequest->fileUpload;
        $customer->Phone=$customerRequest->Phone;
        $customer->Mobile=$customerRequest->Mobile;
        $customer->Email=$customerRequest->Email;
        $customer->Address=$customerRequest->Address;
        $customer->postCode=$customerRequest->postCode;
        $customer->registrationDate=$customerRequest->registrationDate;
        $customer->Description=$customerRequest->Description;
        $customer->company_id=$customerRequest->company_id;
        $customer->region_id=$customerRequest->region_id;
        $customer->createdDate=date('Y-m-d h:i:s');
        $customer->isActive=1;
        $customer->user_id = $userId ?? 0;
        $customer->save();

        //create account for newly added customer
        $account_transaction = new AccountTransaction();
        $account_transaction->Credit=0.00;
        $account_transaction->Debit=0.00;
        $account_transaction->customer_id=$customer->id;
        $account_transaction->user_id=$userId ?? 0;
        $account_transaction->Description='account created';
        $account_transaction->createdDate=date('Y-m-d h:i:s');
        $account_transaction->save();

        return new CustomerResource(Customer::find($customer->id));
    }

    public function update(Request $request, $Id)
    {
        $userId = Auth::id();
        $customer = Customer::find($Id);
        $request['user_id']=$userId ?? 0;
        $customer->update($request->all());
        return new CustomerResource(Customer::find($Id));
    }

    public function getById($Id)
    {
        return new CustomerResource(Customer::find($Id));
    }

    public function BaseList()
    {
        return array('company_type'=>CompanyType::select('id','Name')->orderBy('id','desc')->get(),'payment_type'=>PaymentType::select('id','Name')->orderBy('id','desc')->get(),'payment_term'=>PaymentTerm::select('id','Name')->orderBy('id','desc')->get(),'area_detail'=>$this->get_detail_list());
    }

    public function get_detail_list()
    {
        $region = DB::table('regions as r')->select(
            'r.id',
            'r.Name',
            'r.city_id',
            'ct.Name as city_name',
            'ct.state_id',
            'st.Name as state_name',
            'st.country_id',
            'cnt.name as country_name',
        )->where('r.deleted_at',NULL)
            ->leftjoin('cities as ct', 'ct.id', '=', 'r.city_id')
            ->leftjoin('states as st', 'st.id', '=', 'ct.state_id')
            ->leftjoin('countries as cnt', 'cnt.id', '=', 'st.country_id')->get();
        $region = json_decode(json_encode($region), true);
        return $region;
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

    public function ActivateDeactivate($Id)
    {
        $customer = Customer::find($Id);
        if($customer->isActive==1)
        {
            $customer->isActive=0;
        }
        else
        {
            $customer->isActive=1;
        }
        $customer->update();
        return new CustomerResource(Customer::find($Id));
    }
}
