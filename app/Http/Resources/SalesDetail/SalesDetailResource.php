<?php

namespace App\Http\Resources\SalesDetail;

use Illuminate\Http\Resources\Json\JsonResource;

class SalesDetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'sale_id' => $this->sale_id,
            'product_id'=>$this->product_id,
            'company_id'=>$this->company_id,
            'vehicle_id'=>$this->vehicle_id,
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
            'product'=>$this->product,
        ];
    }
}
