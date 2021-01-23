<?php

namespace App\Http\Resources\AccountTransaction;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountTransactionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'Credit' => $this->Credit,
            'Debit' => $this->Debit,
            'Differentiate' => $this->Differentiate,
            'referenceNumber' => $this->referenceNumber,
            'customer_id' => $this->customer_id,
            'supplier_id' => $this->supplier_id,
            'employee_id' => $this->employee_id,
            'Description' => $this->Description,
            'user_id'=>$this->user_id,
            'createdDate'=>$this->createdDate,
            'customer'=>$this->customer,
            'supplier'=>$this->supplier,
            'employee'=>$this->employee,
        ];
    }
}
