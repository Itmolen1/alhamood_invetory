<?php

namespace App\Http\Resources\CustomerAdvance;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerAdvanceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'receiptNumber' => $this->receiptNumber,
            'paymentType' => $this->paymentType,
            'Amount' => $this->Amount,
            'sumOf' => $this->sumOf,
            'receiverName' => $this->receiverName,
            'Description' => $this->Description,
            'user_id' => $this->user_id,
            'bank_id' => $this->bank_id,
            'accountNumber' => $this->accountNumber,
            'TransferDate' => $this->TransferDate,
            'registerDate' => $this->registerDate,
            'createdDate' => $this->createdDate,
            'isActive'=>$this->isActive,
            'isPushed'=>$this->isPushed,
            'api_customer'=>$this->api_customer,
            //'deleted_at'=>$this->deleted_at,
            //'updated_at'=>$this->updated_at->diffForHumans(),
        ];
    }
}
