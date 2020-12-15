<?php

namespace App\Http\Resources\SupplierPayment;

use App\Http\Resources\SupplierPaymentDetail\SupplierPaymentDetailResource;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierPaymentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'totalAmount' => $this->totalAmount,
            'paidAmount' => $this->paidAmount,
            'amountInWords' => $this->amountInWords,
            'supplier_id' => $this->supplier_id,
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
            'supplierPaymentDate'=>$this->supplierPaymentDate,
            'isPushed'=>$this->isPushed,
            'deleted_at'=>$this->deleted_at,
            'updated_at'=>$this->updated_at->diffForHumans(),
            'supplier_payment_details'=>SupplierPaymentDetailResource::collection($this->whenLoaded('supplier_payment_details')),
        ];
    }
}
