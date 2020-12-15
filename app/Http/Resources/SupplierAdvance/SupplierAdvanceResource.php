<?php


namespace App\Http\Resources\SupplierAdvance;


use Illuminate\Http\Resources\Json\JsonResource;

class SupplierAdvanceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'supplier_id' => $this->supplier_id,
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
            'api_supplier'=>$this->api_supplier,
            //'deleted_at'=>$this->deleted_at,
            //'updated_at'=>$this->updated_at->diffForHumans(),
        ];
    }
}
