<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
        use HasFactory;
        use SoftDeletes;


        protected $guarded=[];
        protected $primaryKey = 'id';
        protected $table = 'purchases';

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

    public function supplier()
    {
        return $this->belongsTo('App\Models\Supplier','supplier_id','id');
    }

    public function api_supplier()
    {
        return $this->belongsTo('App\Models\Supplier','supplier_id','id')->withTrashed();
    }

    public function purchase_details()
    {
        return $this->hasMany('App\Models\PurchaseDetail','purchase_id')->withTrashed();
    }

    public function purchase_details_without_trash()
    {
        return $this->hasMany('App\Models\PurchaseDetail','purchase_id');
    }

    public function update_notes()
    {
        return $this->hasMany('App\Models\UpdateNote','RelationId')->where('RelationTable','=','purchases');
    }

    public function documents()
    {
        return $this->hasMany('App\Models\FileUpload','RelationId')->where('RelationTable','=','purchases');
    }

    public function supplier_payment_details()
    {
        return $this->hasMany('App\Models\SupplierPaymentDetail');
    }

}
