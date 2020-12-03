<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use HasFactory;
    use SoftDeletes;


    protected $guarded=[];
    protected $primaryKey = 'id';
    protected $table = 'Drivers';

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function api_user()
    {
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id','id');
    }

    public function api_company()
    {
        return $this->belongsTo('App\Models\Company','company_id','id')->withTrashed();
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Customer','customer_id','id');
    }

    public function api_customer()
    {
        return $this->belongsTo('App\Models\Customer','customer_id','id')->withTrashed();
    }
}
