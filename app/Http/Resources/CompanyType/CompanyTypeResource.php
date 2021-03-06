<?php

namespace App\Http\Resources\CompanyType;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyTypeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'Name' => $this->Name,
            'Description' => $this->Description,
            'user_id'=>$this->user_id,
            'company_id'=>$this->company_id,
            'isActive'=>$this->isActive,
            'deleted_at'=>$this->deleted_at,
            'updated_at'=>$this->updated_at->diffForHumans(),
            'api_user'=>$this->api_user,
        ];
    }
}
