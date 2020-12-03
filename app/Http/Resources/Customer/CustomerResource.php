<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'Address' => $this->Address,
            'imageUrl' => $this->imageUrl,
            'postCode' => $this->postCode,
            'registrationDate' => $this->registrationDate,
            'Description' => $this->Description,
            'updateDescription' => $this->updateDescription,
            'user_id'=>$this->user_id,
            'company_id'=>$this->company_id,
            'region_id'=>$this->region_id ,
            'api_user'=>$this->api_user,
            'isActive'=>$this->isActive,
            'deleted_at'=>$this->deleted_at,
            'updated_at'=>$this->updated_at->diffForHumans(),
            'api_payment_type' => $this->api_payment_type,
            'api_company_type' => $this->api_company_type,
            'api_payment_term' => $this->api_payment_term,
        ];
    }
}
