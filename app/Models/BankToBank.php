<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankToBank extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded=[];
    protected $primaryKey = 'id';
    protected $table = 'bank_to_banks';

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id','id');
    }

    public function from_bank()
    {
        return $this->belongsTo('App\Models\Bank','from_bank_id','id');
    }

    public function to_bank()
    {
        return $this->belongsTo('App\Models\Bank','to_bank_id','id');
    }
}
