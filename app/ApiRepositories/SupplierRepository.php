<?php


namespace App\ApiRepositories;


use App\ApiRepositories\Interfaces\ISupplierRepositoryInterface;
use App\Http\Requests\SupplierRequest;
use App\Http\Resources\Supplier\SupplierResource;
use App\Models\AccountTransaction;
use App\Models\Company;
use App\Models\CompanyType;
use App\Models\PaymentTerm;
use App\Models\PaymentType;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupplierRepository implements ISupplierRepositoryInterface
{
    public function all()
    {
        //return SupplierResource::collection(Supplier::all()->sortDesc());
        return SupplierResource::collection(Supplier::withTrashed()->get()->sortDesc());
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
        $supplier->Email=$request->Email;
        $supplier->Address=$request->Address;
        $supplier->postCode=$request->postCode;
        $supplier->registrationDate=$request->registrationDate;
        $supplier->Description=$request->Description;
        $supplier->region_id=$request->region_id;
        $supplier->createdDate=date('Y-m-d h:i:s');
        $supplier->isActive=1;
        $supplier->user_id = $userId ?? 0;
        $supplier->company_id=Str::getCompany($userId);
        $supplier->save();

        //create account for newly added customer
        $account_transaction = new AccountTransaction();
        $account_transaction->Credit=0.00;
        $account_transaction->Debit=0.00;
        $account_transaction->supplier_id=$supplier->id;
        $account_transaction->user_id=$userId ?? 0;
        $account_transaction->Description='account created';
        $account_transaction->createdDate=date('Y-m-d h:i:s');
        $account_transaction->save();

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

//    public function get_detail_list()
//    {
//        $country = DB::table('countries')->select(
//            'id',
//            'Name'
//        )->where('deleted_at',NULL)->get();
//        $country = json_decode(json_encode($country), true);
//        for($i=0;$i<count($country);$i++)
//        {
//            $state = DB::table('states as s')->select(
//                's.id',
//                's.Name',
//                's.country_id',
//                'c.Name as country_name'
//            )->where([['s.deleted_at',NULL],['s.id',$country[$i]['id']]])->leftjoin('countries as c', 'c.id', '=', 's.id')->get();
//            $state = json_decode(json_encode($state), true);
//            for($j=0;$j<count($state);$j++)
//            {
//                $state_id_here=$state[$j]['id'];
//                $city = DB::table('cities as ct')->select(
//                    'ct.id',
//                    'ct.Name',
//                    'ct.state_id',
//                    's.Name as state_name'
//                )->where([['ct.deleted_at',NULL],['ct.state_id',$state_id_here]])
//                    ->leftjoin('states as s', 'ct.state_id', '=', 's.id')->get();
//                $city = json_decode(json_encode($city), true);
//                for($k=0;$k<count($city);$k++)
//                {
//                    $city_id_here=$city[$k]['id'];
//                    $region = DB::table('regions as region')->select(
//                        'region.id',
//                        'region.Name',
//                        'region.city_id',
//                        'city.Name as city_name'
//                    )->where([['region.deleted_at',NULL],['region.city_id',$city_id_here]])
//                        ->leftJoin('cities as city','region.city_id','=','city.id')->get();
//                    $region = json_decode(json_encode($region),true);
//                    $city[$k]['region']=$region;
//                }
//                $state[$j]['cities']=$city;
//            }
//            $country[$i]['states']=$state;
//        }
//        return $country;
//    }
}
