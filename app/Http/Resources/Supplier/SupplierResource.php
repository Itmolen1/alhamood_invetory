<?php

namespace App\Http\Resources\Supplier;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use stdClass;

class SupplierResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'Name' => $this->Name,
            'Representative' => $this->Representative,
            'TRNNumber' => $this->TRNNumber,
            'fileUpload' => $this->fileUpload,
            'Phone' => $this->Phone,
            'Mobile' => $this->Mobile,
            'Email' => $this->Email,
            'Address' => $this->Address,
            'imageUrl' => $this->imageUrl,
            'postCode' => $this->postCode,
            'registrationDate' => $this->registrationDate,
            'Description' => $this->Description,
            'updateDescription' => $this->updateDescription,
            'user_id'=>$this->user_id,
            //'company_id'=>$this->company_id,
            'region_id'=>$this->region_id ,
            'region'=>$this->get_detail_list($this->region_id),
            'api_user'=>$this->api_user,
            'isActive'=>$this->isActive,
            'deleted_at'=>$this->deleted_at,
            'updated_at'=>$this->updated_at->diffForHumans(),
            'api_payment_type'=>$this->api_payment_type,
            'api_company_type'=>$this->api_company_type,
            'api_payment_term'=>$this->api_payment_term,
        ];
    }

    public function get_detail_list($region_id)
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
        )->where('r.deleted_at',NULL)->where('r.id','=',$region_id)
            ->leftjoin('cities as ct', 'ct.id', '=', 'r.city_id')
            ->leftjoin('states as st', 'st.id', '=', 'ct.state_id')
            ->leftjoin('countries as cnt', 'cnt.id', '=', 'st.country_id')->get();
        return $region->first();
    }
}
