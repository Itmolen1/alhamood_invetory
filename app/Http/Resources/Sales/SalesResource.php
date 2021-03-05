<?php

namespace App\Http\Resources\Sales;

use App\Http\Resources\FileUpload\FileUploadResource;
use App\Http\Resources\SalesDetail\SalesDetailResource;
use App\Http\Resources\UpdateNote\UpdateNoteResource;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'SaleNumber' => $this->SaleNumber,
            'customer_id' => $this->customer_id,
            'SaleDate' => $this->SaleDate,
            'Total' => $this->Total,
            'subTotal' => $this->subTotal,
            'totalVat' => $this->totalVat,
            'grandTotal' => $this->grandTotal,
            'paidBalance' => $this->paidBalance,
            'remainingBalance' => $this->remainingBalance,
            'Description' => $this->Description,
            'IsPaid' => $this->IsPaid,
            'IsPartialPaid' => $this->IsPartialPaid,
            'company_id'=>$this->company_id,
            'createdDate'=>$this->createdDate,
            'isActive'=>$this->isActive,
            'deleted_at'=>$this->deleted_at,
            'updated_at'=>$this->updated_at->diffForHumans(),
            'api_user'=>$this->api_user,
            'api_customer'=>$this->api_customer,
            //'user'=>UserResource::collection($this->whenLoaded('user')),
            'sale_details'=>SalesDetailResource::collection($this->whenLoaded('sale_details')),
            'update_notes'=>UpdateNoteResource::collection($this->whenLoaded('update_notes')),
            'documents'=>FileUploadResource::collection($this->whenLoaded('documents')),
        ];
    }
}
