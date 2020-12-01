<?php

namespace App\Http\Resources\Bank;

use Illuminate\Http\Resources\Json\JsonResource;

class BankResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'Name' => $this->Name,
            'Branch' => $this->Branch,
            'Description' => $this->Description,
            'contactNumber' => $this->contactNumber,
            'Address' => $this->Address,
            'user_id'=>$this->user_id,
            'user'=>$this->user,
            'isActive'=>$this->isActive,
            'deleted_at'=>$this->deleted_at,
            'updated_at'=>$this->updated_at->diffForHumans(),
        ];
    }
}
