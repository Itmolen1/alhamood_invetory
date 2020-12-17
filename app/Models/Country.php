<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use HasFactory;
    use SoftDeletes;


    protected $guarded=[];
    protected $casts = [
        'id'=>'integer'
    ];
    protected $primaryKey = 'id';
    protected $table = 'countries';

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id','id');
    }

    public function states()
    {
        return $this->hasMany('App\Models\State');
    }
}
