<?php

namespace App\Http\Resources\Supplier;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'Name' => $this->Name,
            'Representative' => $this->Representative,
            'companyType' => $this->companyType,
            'paymentType' => $this->paymentType,
            'paymentTerm' => $this->paymentTerm,
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
            'user'=>$this->user,
            'isActive'=>$this->isActive,
            'deleted_at'=>$this->deleted_at,
            'updated_at'=>$this->updated_at->diffForHumans(),
        ];
    }
}
