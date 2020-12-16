<?php

namespace App\Http\Resources\SupplierPaymentDetail;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplierPaymentDetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'amountPaid' => $this->amountPaid,
            'supplier_payment_id'=>$this->supplier_payment_id,
            'user_id'=>$this->user_id,
            'company_id'=>$this->company_id,
            'purchase_id'=>$this->purchase_id,
            'api_purchase'=>$this->api_purchase,
            'Description'=>$this->Description,
            'supplierPaymentDetailDate'=>$this->supplierPaymentDetailDate,
            'createdDate'=>$this->createdDate,
            'isActive'=>$this->isActive,
            'deleted_at'=>$this->deleted_at,
            'updated_at'=>$this->updated_at->diffForHumans(),
            //'api_product'=>new ProductResource($this->api_product),
            //'api_vehicle'=>$this->api_vehicle,
        ];
    }
}
