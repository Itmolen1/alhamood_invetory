<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded=[];
    protected $primaryKey = 'id';
    protected $table = 'customers';

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

    public function  customer_advances()
    {
        return $this->hasMany('App\Models\CustomerAdvance');
    }

    public function drivers()
    {
        return $this->hasMany('App\Models\Driver');
    }

    public function vehicles()
    {
        return $this->hasMany('App\Models\Vehicle');
    }

    public function region()
    {
        return $this->belongsTo('App\Models\Region');
    }

    public function sales()
    {
        return $this->hasMany('App\Models\Sale');
    }

    public function loans()
    {
        return $this->hasMany('App\Models\Loan');
    }

    public function customer_prices()
    {
        return $this->hasMany('App\Models\CustomerPrice');
    }

    public function account_transaction()
    {
        return $this->hasMany('App\Models\AccountTransaction');
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

    public function api_payment_type()
    {
        return $this->belongsTo('App\Models\PaymentType','payment_type_id','id')->withTrashed();
    }

    public function api_company_type()
    {
        return $this->belongsTo('App\Models\CompanyType','company_type_id','id')->withTrashed();
    }

    public function api_payment_term()
    {
        return $this->belongsTo('App\Models\PaymentTerm','payment_term_id','id')->withTrashed();
    }

    public function payment_receives()
    {
        return $this->hasMany('App\Models\PaymentReceive');
    }
}
