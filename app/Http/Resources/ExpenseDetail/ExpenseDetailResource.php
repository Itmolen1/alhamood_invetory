<?php

namespace App\Http\Resources\ExpenseDetail;

use App\Http\Controllers\api\ExpenseCategory;
use App\Http\Resources\ExpenseCategory\ExpenseCategoryResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseDetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'expense_id' => $this->expense_id,
            'user_id'=>$this->user_id,
            'company_id'=>$this->company_id,
            'expense_category_id'=>$this->expense_category_id,
            'createdDate'=>$this->createdDate,
            'expenseDate'=>$this->expenseDate,
            'PadNumber'=>$this->PadNumber,
            'Description'=>$this->Description,
            'Total'=>$this->Total,
            'VAT'=>$this->VAT,
            'rowVatAmount'=>$this->rowVatAmount,
            'rowSubTotal'=>$this->rowSubTotal,
            'deleted_at'=>$this->deleted_at,
            'updated_at'=>$this->updated_at->diffForHumans(),
            //'product'=>$this->product,
            'api_expense_category'=>$this->api_expense_category,
        ];
    }
}
