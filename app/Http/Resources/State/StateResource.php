<?php

namespace App\Http\Resources\State;

use Illuminate\Http\Resources\Json\JsonResource;

class StateResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'country_id' => $this->country_id,
            'Name' => $this->Name,
            'isActive'=>$this->isActive,
            'country'=>$this->country,
            //'deleted_at'=>$this->deleted_at,
            //'updated_at'=>$this->updated_at->diffForHumans(),
        ];
    }
}
