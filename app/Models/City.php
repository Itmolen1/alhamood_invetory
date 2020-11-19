<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
        use HasFactory;
        use SoftDeletes;


        protected $guarded=[];
        protected $primaryKey = 'id';
        protected $table = 'cities';

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id','id');
    }

    public function state()
    {
        return $this->belongsTo('App\Models\State','state_id','id');
    }

    public function region()
    {
        return $this->hasMany('App\Models\Region');
    }
}
