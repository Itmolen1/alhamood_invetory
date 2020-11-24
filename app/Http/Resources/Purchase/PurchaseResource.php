<?php

namespace App\Http\Resources\Purchase;

use App\Http\Resources\PurchaseDetail\PurchaseDetailResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'PurchaseNumber' => $this->PurchaseNumber,
            'PurchaseDate' => $this->PurchaseDate,
            'user_id'=>$this->user_id,
            'user'=>$this->user,
            'company_id'=>$this->company_id,
            'isActive'=>$this->isActive,
            'deleted_at'=>$this->deleted_at,
            'updated_at'=>$this->updated_at->diffForHumans(),
            'purchase_details'=>PurchaseDetailResource::collection($this->whenLoaded('purchase_details')),
        ];
    }
}
