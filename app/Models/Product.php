<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
        use HasFactory;
        use SoftDeletes;


        protected $guarded=[];
        protected $primaryKey = 'id';
        protected $table = 'products';

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id','id');
    }

    public function units()
    {
        return $this->hasMany('App\Models\Unit');
    }

    public function api_units()
    {
        return $this->hasMany('App\Models\Unit')->withTrashed();
    }

    public function sale_Details()
    {
        return $this->hasMany('App\Models\SaleDetail');
    }
}
