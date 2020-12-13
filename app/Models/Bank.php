<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
        use HasFactory;
        use SoftDeletes;


        protected $guarded=[];
        protected $primaryKey = 'id';
        protected $table = 'banks';

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

    public function customer_advances()
    {
        return $this->hasMany('App\Models\CustomerAdvance');
    }

    public function supplier_advances()
    {
        return $this->hasMany('App\Models\SupplierAdvance');
    }

    public function payment_receives()
    {
        return $this->hasMany('App\Models\PaymentReceive');
    }

    public function supplier_payments()
    {
        return $this->hasMany('App\Models\SupplierPayment');
    }
}
