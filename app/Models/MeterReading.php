<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeterReading extends Model
{
        use HasFactory;
        use SoftDeletes;


        protected $guarded=[];
        protected $primaryKey = 'id';
        protected $table = 'meter_readings';

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id','id');
    }

    public function meter_reading_details()
    {
        return $this->hasMany('App\Models\MeterReadingDetail','meter_reading_id')->withTrashed();
    }

    public function update_notes()
    {
        return $this->hasMany('App\Models\UpdateNote','RelationId')->where('RelationTable','=','meter_readings');
    }
}
