<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierAdvanceDetail extends Model
{
        use HasFactory;
        use SoftDeletes;


        protected $guarded=[];
        protected $primaryKey = '';
        protected $table = '';
}
