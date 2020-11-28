<?php

namespace App\Http\Resources\MeterReadingDetail;

use Illuminate\Http\Resources\Json\JsonResource;

class MeterReadingDetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'startReading' => $this->startReading,
            'endReading' => $this->endReading,
            'netReading' => $this->netReading,
            'Purchases' => $this->Purchases,
            'Sales' => $this->Sales,
            'user_id'=>$this->user_id,
            'meter_reading_id'=>$this->meter_reading_id,
            'company_id'=>$this->company_id,
            'meter_reader_id'=>$this->meter_reader_id,
            'Description'=>$this->Description,
            'meterDate'=>$this->meterDate,
            //'user'=>$this->user,
            'isActive'=>$this->isActive,
            'deleted_at'=>$this->deleted_at,
            'updated_at'=>$this->updated_at->diffForHumans(),
        ];
    }
}
