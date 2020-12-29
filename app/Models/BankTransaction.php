<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankTransaction extends Model
{
        use HasFactory;
        use SoftDeletes;

        protected $primaryKey = 'id';
        protected $table = 'bank_transactions';
}
