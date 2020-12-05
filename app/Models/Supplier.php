<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use stdClass;

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

    public function api_user()
    {
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
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

//    public function get_detail_list($region_id)
//    {
//        $region = DB::table('regions as r')->select(
//            'r.id',
//            'r.Name',
//            'r.city_id',
//            'ct.Name as city_name',
//            'ct.state_id',
//            'st.Name as state_name',
//            'st.country_id',
//            'cnt.name as country_name',
//        )->where('r.deleted_at',NULL)->where('r.id','=',$region_id)
//            ->leftjoin('cities as ct', 'ct.id', '=', 'r.city_id')
//            ->leftjoin('states as st', 'st.id', '=', 'ct.state_id')
//            ->leftjoin('countries as cnt', 'cnt.id', '=', 'st.country_id')->get();
//        //$region = json_decode(json_encode($region), false);
//        //$region = $region->toArray();
//        //echo "<pre>";print_r($region);die;
//        return $region->first;
//    }

}
