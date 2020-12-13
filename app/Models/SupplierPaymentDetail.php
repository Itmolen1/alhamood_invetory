<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierPaymentDetail extends Model
{
        use HasFactory;
        use SoftDeletes;


        protected $guarded=[];
        protected $primaryKey = 'id';
        protected $table = 'supplier_payment_details';

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id','id');
    }

    public function supplier_payment()
    {
        return $this->belongsTo('App\Models\SupplierPayment','supplier_payment_id','id');
    }

    public function purchase()
    {
        return $this->belongsTo('App\Models\Purchase','purchase_id','id');
    }
}
