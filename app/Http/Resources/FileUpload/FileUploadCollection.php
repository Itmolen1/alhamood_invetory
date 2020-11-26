<?php


namespace App\Http\Resources\FileUpload;


use Illuminate\Http\Resources\Json\ResourceCollection;

class FileUploadCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
