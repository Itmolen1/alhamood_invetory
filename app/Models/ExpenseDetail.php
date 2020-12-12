<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseDetail extends Model
{
        use HasFactory;
        use SoftDeletes;


        protected $guarded=[];
        protected $primaryKey = 'id';
        protected $table = 'expense_details';

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id','id');
    }


    public function expense()
    {
        return $this->belongsTo('App\Models\Expense','expense_id','id');
    }

    public function expense_category()
    {
        return $this->belongsTo('App\Models\ExpenseCategory','expense_category_id','id');
    }

    public function api_expense_category()
    {
        return $this->belongsTo('App\Models\ExpenseCategory','expense_category_id','id')->withTrashed();
    }
}
