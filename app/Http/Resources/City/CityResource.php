<?php

namespace App\Http\Resources\City;

use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'state_id' => $this->state_id,
            'Name' => $this->Name,
            'isActive'=>$this->isActive,
            'state'=>$this->state,
            //'deleted_at'=>$this->deleted_at,
            //'updated_at'=>$this->updated_at->diffForHumans(),
        ];
    }
}
