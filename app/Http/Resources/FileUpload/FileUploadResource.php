<?php

namespace App\Http\Resources\FileUpload;

use Illuminate\Http\Resources\Json\JsonResource;

class FileUploadResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            //'Title' => $this->Title,
            'Title' =>asset('storage/document/'.$this->Title),
            //'RelationTable' => $this->RelationTable,
            //'RelationId' => $this->RelationId
        ];
    }
}
