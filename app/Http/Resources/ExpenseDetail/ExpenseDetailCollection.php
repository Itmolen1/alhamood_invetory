<?php


namespace App\Http\Resources\ExpenseDetail;


use Illuminate\Http\Resources\Json\ResourceCollection;

class ExpenseDetailCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
