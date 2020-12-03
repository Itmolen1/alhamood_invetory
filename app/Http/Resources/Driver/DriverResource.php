<?php

namespace App\Http\Resources\Driver;

use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'driverName' => $this->driverName,
            'Description' => $this->Description,
            'customer_id' => $this->customer_id,
            'api_customer' => $this->api_customer,
            'company_id' => $this->company_id,
            'api_company' => $this->api_company,
            'user_id'=>$this->user_id,
            'api_user'=>$this->api_user,
            'isActive'=>$this->isActive,
            'deleted_at'=>$this->deleted_at,
            'updated_at'=>$this->updated_at->diffForHumans(),
        ];
    }
}
