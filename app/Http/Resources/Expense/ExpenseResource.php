<?php

namespace App\Http\Resources\Expense;

use App\Http\Resources\ExpenseDetail\ExpenseDetailResource;
use App\Http\Resources\UpdateNote\UpdateNoteResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'expenseNumber' => $this->expenseNumber,
            'user_id' => $this->user_id,
            'company_id' => $this->company_id,
            'employee_id' => $this->employee_id,
            'api_employee' => $this->api_employee,
            'supplier_id' => $this->supplier_id,
            'api_supplier' => $this->api_supplier,
            'expenseDate' => $this->expenseDate,
            'referenceNumber' => $this->referenceNumber,
            'Total' => $this->Total,
            'subTotal' => $this->subTotal,
            'totalVat' => $this->totalVat,
            'grandTotal' => $this->grandTotal,
            'paidBalance' => $this->paidBalance,
            'remainingBalance' => $this->remainingBalance,
            'Description' => $this->Description,
            'TermsAndCondition' => $this->TermsAndCondition,
            'supplierNote' => $this->supplierNote,
            'isApprove' => $this->isApprove,
            'isDelay' => $this->isDelay,
            'createdDate'=>$this->createdDate,
            'isActive'=>$this->isActive,
            'deleted_at'=>$this->deleted_at,
            'updated_at'=>$this->updated_at->diffForHumans(),
            'api_user'=>$this->api_user,
            //'api_customer'=>$this->api_customer,
            //'user'=>UserResource::collection($this->whenLoaded('user')),
            'expense_details'=>ExpenseDetailResource::collection($this->whenLoaded('expense_details')),
            'update_notes'=>UpdateNoteResource::collection($this->whenLoaded('update_notes')),
            //'documents'=>FileUploadResource::collection($this->whenLoaded('documents')),
        ];
    }
}
