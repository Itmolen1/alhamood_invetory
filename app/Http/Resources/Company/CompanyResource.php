<?php

namespace App\Http\Resources\Company;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'Name' => $this->Name,
            'Representative' => $this->Representative,
            'Phone' => $this->Phone,
            'Mobile' => $this->Mobile,
            'Address' => $this->Address,
            'postCode' => $this->postCode,
            'Description' => $this->Description,
            'user_id'=>$this->user_id,
            'company_id'=>$this->company_id,
            'region_id'=>$this->region_id ,
            'api_user'=>$this->api_user,
            'isActive'=>$this->isActive,
            'deleted_at'=>$this->deleted_at,
            'updated_at'=>$this->updated_at->diffForHumans(),
        ];
    }
}
