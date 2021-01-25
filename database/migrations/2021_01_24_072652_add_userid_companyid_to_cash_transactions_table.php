<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUseridCompanyidToCashTransactionsTable extends Migration
{
    public function up()
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->default(0);
            $table->unsignedBigInteger('company_id')->default(0);
        });
    }

    public function down()
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->dropColumn('company_id');
        });
    }
}
