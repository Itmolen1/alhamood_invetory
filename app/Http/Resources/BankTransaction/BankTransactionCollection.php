<?php


namespace App\Http\Resources\BankTransaction;


use Illuminate\Http\Resources\Json\ResourceCollection;

class BankTransactionCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
