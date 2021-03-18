<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFinancerIdToAccountTransactionsTable extends Migration
{
    public function up()
    {
        Schema::table('account_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('financer_id')->default(0)->index();
        });
    }

    public function down()
    {
        Schema::table('account_transactions', function (Blueprint $table) {
            $table->dropColumn('financer_id');
        });
    }
}
