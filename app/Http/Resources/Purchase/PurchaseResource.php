<?php

namespace App\Http\Resources\Purchase;

use App\Http\Resources\FileUpload\FileUploadResource;
use App\Http\Resources\PurchaseDetail\PurchaseDetailResource;
use App\Http\Resources\UpdateNote\UpdateNoteResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'PurchaseNumber' => $this->PurchaseNumber,
            'PurchaseDate' => $this->PurchaseDate,
            'DueDate' => $this->DueDate,
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
            'IsPaid' => $this->IsPaid,
            'IsPartialPaid' => $this->IsPartialPaid,
            'IsNeedStampOrSignature' => $this->IsNeedStampOrSignature,
            'company_id'=>$this->company_id,
            'isActive'=>$this->isActive,
            'deleted_at'=>$this->deleted_at,
            'updated_at'=>$this->updated_at->diffForHumans(),
            'api_user'=>$this->api_user,
            'api_supplier'=>$this->api_supplier,
            //'user'=>UserResource::collection($this->whenLoaded('user')),
            'purchase_details'=>PurchaseDetailResource::collection($this->whenLoaded('purchase_details')),
            'purchase_details_without_trash'=>PurchaseDetailResource::collection($this->whenLoaded('purchase_details_without_trash')),
            'update_notes'=>UpdateNoteResource::collection($this->whenLoaded('update_notes')),
            'documents'=>FileUploadResource::collection($this->whenLoaded('documents')),
        ];
    }
}
