<?php

namespace App\Http\Resources\MeterReading;

use App\Http\Resources\MeterReadingDetail\MeterReadingDetailResource;
use App\Http\Resources\UpdateNote\UpdateNoteResource;
use Illuminate\Http\Resources\Json\JsonResource;

class MeterReadingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'readingDate' => $this->readingDate,
            'startPad' => $this->startPad,
            'endPad' => $this->endPad,
            'totalPadSale' => $this->totalPadSale,
            'totalMeterSale' => $this->totalMeterSale,
            'saleDifference' => $this->saleDifference,
            'Description' => $this->Description,
            'user_id'=>$this->user_id,
            'employee_id'=>$this->employee_id,
            'user'=>$this->user,
            'company_id'=>$this->company_id,
            'isActive'=>$this->isActive,
            'deleted_at'=>$this->deleted_at,
            'updated_at'=>$this->updated_at->diffForHumans(),
            'meter_reading_details'=>MeterReadingDetailResource::collection($this->whenLoaded('meter_reading_details')),
            'update_notes'=>UpdateNoteResource::collection($this->whenLoaded('update_notes')),
        ];
    }
}
