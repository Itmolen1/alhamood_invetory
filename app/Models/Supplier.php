<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory;
    use SoftDeletes;


    protected $guarded=[];
    protected $primaryKey = 'id';
    protected $table = 'suppliers';


    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id','id');
    }

    public function supplier_advances()
    {
        return $this->hasMany('App\Models\SupplierAdvance');
    }

    public function region()
    {
        return $this->belongsTo('App\Models\Region','region_id','id');
    }

    public function purchases()
    {
        return $this->hasMany('App\Models\Purchase');
    }

    public function expenses()
    {
        return $this->hasMany('App\Models\Expense');
    }

    public function account_transaction()
    {
        return $this->hasOne('App\Models\AccountTransaction');
    }

    public function payment_type()
    {
        return $this->belongsTo('App\Models\PaymentType','payment_type_id','id');
    }

    public function company_type()
    {
        return $this->belongsTo('App\Models\CompanyType','company_type_id','id');
    }

    public function payment_term()
    {
        return $this->belongsTo('App\Models\PaymentTerm','payment_term_id','id');
    }

}
