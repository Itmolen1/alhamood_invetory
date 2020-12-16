<?php

namespace App\Http\Resources\PaymentReceiveDetail;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentReceiveDetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'amountPaid' => $this->amountPaid,
            'payment_receive_id'=>$this->payment_receive_id,
            'user_id'=>$this->user_id,
            'company_id'=>$this->company_id,
            'sale_id'=>$this->sale_id,
            'api_sale'=>$this->api_sale,
            'Description'=>$this->Description,
            'paymentReceiveDetailDate'=>$this->paymentReceiveDetailDate,
            'createdDate'=>$this->createdDate,
            'isActive'=>$this->isActive,
            'deleted_at'=>$this->deleted_at,
            'updated_at'=>$this->updated_at->diffForHumans(),
            //'api_product'=>new ProductResource($this->api_product),
            //'api_vehicle'=>$this->api_vehicle,
        ];
    }
}
