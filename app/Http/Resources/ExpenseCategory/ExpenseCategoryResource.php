<?php

namespace App\Http\Resources\ExpenseCategory;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseCategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'Name' => $this->Name,
            'Description' => $this->Description,
            'user_id'=>$this->user_id,
            'company_id'=>$this->company_id,
            'user'=>$this->user,
            'isActive'=>$this->isActive,
            'deleted_at'=>$this->deleted_at,
            'updated_at'=>$this->updated_at->diffForHumans(),
        ];
    }
}
