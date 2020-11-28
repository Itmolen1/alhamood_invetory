<?php

namespace App\Http\Resources\MeterReader;

use Illuminate\Http\Resources\Json\JsonResource;

class MeterReaderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'Name' => $this->Name,
            'shortDescriptionForm' => $this->shortDescriptionForm,
            'user_id'=>$this->user_id,
            'user'=>$this->user,
            'company_id'=>$this->company_id,
            'isActive'=>$this->isActive,
            'deleted_at'=>$this->deleted_at,
            'updated_at'=>$this->updated_at->diffForHumans(),
        ];
    }
}
