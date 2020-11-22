<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'FullName' => $this->FullName,
            'email' => $this->email,
            'UserName_verified_at' => $this->UserName_verified_at,
            'DateOfBirth' => $this->DateOfBirth,
            'ImageUrl' => $this->ImageUrl,
            'ContactNumber' => $this->ContactNumber,
            'Address' => $this->Address,
            'CreatedDate' => $this->CreatedDate,
            'IsActive' => $this->IsActive,
            'region_Id' => $this->regions_Id,
            'role_Id' => $this->role_Id,
            'gender_Id' => $this->genders_Id,
            'region' => $this->region,
            'gender' => $this->genders,
            'role' => $this->role,
            'actionToRoles' => $this->role->actionToRoles ?? 0,

        ];
    }
}
