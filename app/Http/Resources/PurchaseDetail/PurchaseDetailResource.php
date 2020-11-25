<?php

namespace App\Http\Resources\PurchaseDetail;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PurchaseDetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'purchase_id' => $this->purchase_id,
            'user_id'=>$this->user_id,
            'company_id'=>$this->company_id,
            'product_id'=>$this->product_id,
            'PadNumber'=>$this->PadNumber,
            'Description'=>$this->Description,
            'Quantity'=>$this->Quantity,
            'Price'=>$this->Price,
            'rowTotal'=>$this->rowTotal,
            'VAT'=>$this->VAT,
            'rowVatAmount'=>$this->rowVatAmount,
            'rowSubTotal'=>$this->rowSubTotal,
            'unit_id'=>$this->unit_id,
            'isActive'=>$this->isActive,
            'deleted_at'=>$this->deleted_at,
            'updated_at'=>$this->updated_at->diffForHumans(),
        ];
    }
}
