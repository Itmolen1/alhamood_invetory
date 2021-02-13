<?php

namespace App\Http\Resources\Deposit;

use Illuminate\Http\Resources\Json\JsonResource;

class DepositResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'Amount' => $this->Amount,
            'bank_id' => $this->bank_id,
            'Reference' => $this->Reference,
            'depositDate' => $this->depositDate,
            'user_id' => $this->user_id,
            'company_id' => $this->company_id,
        ];
    }
}
