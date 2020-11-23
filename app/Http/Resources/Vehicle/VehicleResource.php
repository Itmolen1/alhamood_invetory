<?php

namespace App\Http\Resources\Vehicle;

use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'registrationNumber' => $this->registrationNumber,
            'Description' => $this->Description,
            'customer_id' => $this->customer_id,
            'customer' => $this->customer,
            'company_id' => $this->company_id,
            'company' => $this->company,
            'user_id'=>$this->user_id,
            'user'=>$this->user,
            'isActive'=>$this->isActive,
            'deleted_at'=>$this->deleted_at,
            'updated_at'=>$this->updated_at->diffForHumans(),
        ];
    }
}
