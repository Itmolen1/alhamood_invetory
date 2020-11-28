<?php


namespace App\Http\Resources\ExpenseCategory;


use Illuminate\Http\Resources\Json\ResourceCollection;

class ExpenseCategoryCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
