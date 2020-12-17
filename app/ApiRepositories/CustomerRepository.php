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
use Illuminate\Support\Str;

class CustomerRepository implements ICustomerRepositoryInterface
{
    public function all()
    {
        return CustomerResource::collection(Customer::all()->sortDesc());
    }

    public function CustomerSearch(Request $request)
    {
        return CustomerResource::collection(Customer::where('Name','LIKE',"%{$request->Name}%")->get()->sortDesc());
    }

    public function paginate($page_no, $page_size)
    {
        return CustomerResource::Collection(Customer::all()->sortDesc()->forPage($page_no,$page_size));
    }

    public function insert(Request $request)
    {
        $userId = Auth::id();
        $company_id=Str::getCompany($userId);
        $customer = new Customer();
        $customer->Name=$request->Name;
        $customer->Representative=$request->Representative;
        $customer->company_type_id=$request->company_type_id;
        $customer->payment_type_id=$request->payment_type_id;
        $customer->payment_term_id=$request->payment_term_id;
        $customer->TRNNumber=$request->TRNNumber;
        $customer->fileUpload=$request->fileUpload;
        $customer->Phone=$request->Phone;
        $customer->Mobile=$request->Mobile;
        $customer->Email=$request->Email;
        $customer->Address=$request->Address;
        $customer->postCode=$request->postCode;
        $customer->registrationDate=$request->registrationDate;
        $customer->Description=$request->Description;
        $customer->company_id=$company_id;
        $customer->region_id=$request->region_id;
        $customer->createdDate=date('Y-m-d h:i:s');
        $customer->isActive=1;
        $customer->user_id = $userId ?? 0;
        $customer->company_id=Str::getCompany($userId);
        $customer->save();

        //create account for newly added customer
        $account_transaction = new AccountTransaction();
        $account_transaction->Credit=0.00;
        $account_transaction->Debit=0.00;
        $account_transaction->customer_id=$customer->id;
        $account_transaction->user_id=$userId ?? 0;
        $account_transaction->company_id=$company_id ?? 0;
        $account_transaction->Description='account created';
        $account_transaction->createdDate=date('Y-m-d h:i:s');
        $account_transaction->save();

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
