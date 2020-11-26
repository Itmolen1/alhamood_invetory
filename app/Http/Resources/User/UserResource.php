<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'dateOfBirth' => $this->dateOfBirth,
            'imageUrl' =>asset('storage/profile/'.$this->imageUrl),
            //'imageUrl' => url('storage/').$this->imageUrl,
            'contactNumber' => $this->contactNumber,
            'address' => $this->address,
            'createdDate' => $this->createdDate,
            'isActive' => $this->isActive,
            'region_Id' => $this->regions_Id,
            'role_Id' => $this->role_Id,
            'gender_Id' => $this->genders_Id,
            'region' => $this->region,
            'gender' => $this->genders,
            'role' => $this->role,
        ];
    }
}
