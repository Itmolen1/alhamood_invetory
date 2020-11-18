<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory;
    use SoftDeletes;


    protected $guarded=[];
    protected $primaryKey = 'id';
    protected $table = 'companies';

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function users()
    {
        return $this->hasMany('App\Models\User');
    }

    public function customers()
    {
        return $this->hasMany('App\Models\Customer');
    }

    public function customer_advances()
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

    public function suppliers()
    {
        return $this->hasMany('App\Models\Supplier');
    }

    public function supplier_advances()
    {
        return $this->hasMany('App\Models\SupplierAdvance');
    }

    public function banks()
    {
        return $this->hasMany('App\Models\Bank');
    }
        
}
