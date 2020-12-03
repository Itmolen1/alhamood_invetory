<?php

namespace App\Http\Resources\Employee;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'Name' => $this->Name,
            'Mobile' => $this->Mobile,
            'emergencyContactNumber' => $this->emergencyContactNumber,
            'identityNumber' => $this->identityNumber,
            'passportNumber' => $this->passportNumber,
            'Address' => $this->Address,
            'driverLicenceNumber' => $this->driverLicenceNumber,
            'driverLicenceExpiry' => $this->driverLicenceExpiry,
            'startOfJob' => $this->startOfJob,
            'DOB' => $this->DOB,
            //'marital_id' => $this->marital_id,
            //'designation_id' => $this->designation_id,
            //'referenceEmployee_id' => $this->referenceEmployee_id,
            //'shift_id' => $this->shift_id,
            //'department_id' => $this->department_id,
            //'region_id' => $this->region_id,
            //'gender_id' => $this->gender_id,
            'Description' => $this->Description,
            'user_id'=>$this->user_id,
            'api_user'=>$this->api_user,
            //'unit_id'=>$this->unit_id,
            'company_id'=>$this->company_id,
            'isActive'=>$this->isActive,
            'deleted_at'=>$this->deleted_at,
            'updated_at'=>$this->updated_at->diffForHumans(),
        ];
    }
}
