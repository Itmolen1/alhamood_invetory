<?php

namespace App\Http\Resources\PaymentReceive;

use App\Http\Resources\PaymentReceiveDetail\PaymentReceiveDetailResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentReceiveResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'totalAmount' => $this->totalAmount,
            'paidAmount' => $this->paidAmount,
            'amountInWords' => $this->amountInWords,
            'customer_id' => $this->customer_id,
            'user_id' => $this->user_id,
            'company_id'=>$this->company_id,
            'bank_id'=>$this->bank_id,
            'accountNumber'=>$this->accountNumber,
            'transferDate'=>$this->transferDate,
            'payment_type'=>$this->payment_type,
            'referenceNumber'=>$this->referenceNumber,
            'receiverName'=>$this->receiverName,
            'receiptNumber'=>$this->receiptNumber,
            'Description'=>$this->Description,
            'paymentReceiveDate'=>$this->paymentReceiveDate,
            'isPushed'=>$this->isPushed,
            'deleted_at'=>$this->deleted_at,
            'updated_at'=>$this->updated_at->diffForHumans(),
            'payment_receive_details'=>PaymentReceiveDetailResource::collection($this->whenLoaded('payment_receive_details')),
        ];
    }
}
