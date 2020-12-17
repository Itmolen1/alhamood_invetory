<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierPayment extends Model
{
        use HasFactory;
        use SoftDeletes;


        protected $guarded=[];
        protected $primaryKey = 'id';
        protected $table = 'supplier_payments';

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }
    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id','id');
    }

    public function supplier()
    {
        return $this->belongsTo('App\Models\Supplier','supplier_id','id');
    }

    public function api_supplier()
    {
        return $this->belongsTo('App\Models\Supplier','supplier_id','id')->withTrashed();
    }

    public function bank()
    {
        return $this->belongsTo('App\Models\Bank','bank_id','id');
    }

    public function supplier_payment_details()
    {
        return $this->hasMany('App\Models\SupplierPaymentDetail');
    }
}
