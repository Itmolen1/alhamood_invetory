<?php

namespace App\Http\Resources\Loan;

use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'loanTo' => $this->loanTo,
            'payLoan' => $this->payLoan,
            'loanInWords'=>$this->loanInWords,
            'voucherNumber'=>$this->voucherNumber,
            'remainingLoan'=>$this->remainingLoan,
            'user_id'=>$this->user_id,
            'customer_id'=>$this->customer_id,
            'employee_id'=>$this->employee_id,
            'Description'=>$this->Description,
            'loanDate'=>$this->loanDate,
            'isPay'=>$this->isPay,
            'isReturn'=>$this->isReturn,
            'user'=>$this->user,
            'company_id'=>$this->company_id,
            'isActive'=>$this->isActive,
            'deleted_at'=>$this->deleted_at,
            'updated_at'=>$this->updated_at->diffForHumans(),
        ];
    }
}
