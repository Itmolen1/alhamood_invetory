<?php

namespace App\Http\Resources\Region;

use Illuminate\Http\Resources\Json\JsonResource;

class RegionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'city_id' => $this->city_id,
            'Name' => $this->Name,
            'isActive'=>$this->isActive,
            'city'=>$this->city,
            //'deleted_at'=>$this->deleted_at,
            //'updated_at'=>$this->updated_at->diffForHumans(),
        ];
    }
}
