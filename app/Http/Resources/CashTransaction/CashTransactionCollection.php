<?php


namespace App\Http\Resources\CashTransaction;


use Illuminate\Http\Resources\Json\ResourceCollection;

class CashTransactionCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
