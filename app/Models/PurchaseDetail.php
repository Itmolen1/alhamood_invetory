<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseDetail extends Model
{
        use HasFactory;
        use SoftDeletes;


        protected $guarded=[];
        protected $primaryKey = 'id';
        protected $table = 'purchase_details';

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

    public function purchase()
    {
        return $this->belongsTo('App\Models\Purchase','purchase_id','id');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Product','product_id','id');
    }

    public function api_product()
    {
        return $this->belongsTo('App\Models\Product','product_id','id')->withTrashed();
    }

    public function unit()
    {
        return $this->belongsTo('App\Models\Unit','unit_id','id');
    }
}
