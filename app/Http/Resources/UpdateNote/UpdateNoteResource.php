<?php

namespace App\Http\Resources\UpdateNote;

use Illuminate\Http\Resources\Json\JsonResource;

class UpdateNoteResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'RelationTable' => $this->RelationTable,
            'RelationId' => $this->RelationId,
            'Description' => $this->Description,
            'user_id' => $this->user_id,
            'updated_at'=>$this->updated_at->diffForHumans(),
            'user'=>$this->user,
        ];
    }
}
