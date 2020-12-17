<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentReceiveDetail extends Model
{
        use HasFactory;
        use SoftDeletes;


        protected $guarded=[];
        protected $primaryKey = 'id';
        protected $table = 'payment_receive_details';

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id','id');
    }

    public function sale()
    {
        return $this->belongsTo('App\Models\Sale','sale_id','id');
    }

    public function api_sale()
    {
        return $this->belongsTo('App\Models\Sale','sale_id','id')->withTrashed();
    }

    public function payment_receive()
    {
        return $this->belongsTo('App\Models\PaymentReceive','payment_receive_id','id');
    }


}
