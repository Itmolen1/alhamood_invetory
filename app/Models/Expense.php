<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded=[];
    protected $primaryKey = 'id';
    protected $table = 'expenses';

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

    public function api_employee()
    {
        return $this->belongsTo('App\Models\Employee','employee_id','id')->withTrashed();
    }

    public function expense_details()
    {
        return $this->hasMany('App\Models\ExpenseDetail','expense_id');
    }

    public function expense_details_with_trashed()
    {
        return $this->hasMany('App\Models\ExpenseDetail','expense_id')->withTrashed();
    }

    public function update_notes()
    {
        return $this->hasMany('App\Models\UpdateNote','RelationId')->where('RelationTable','=','expenses');
    }

    public function documents()
    {
        return $this->hasMany('App\Models\FileUpload','RelationId')->where('RelationTable','=','expenses');
    }
}
