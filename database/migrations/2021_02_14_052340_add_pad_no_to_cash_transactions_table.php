<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPadNoToCashTransactionsTable extends Migration
{
    public function up()
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->string('PadNumber')->nullable();
        });
    }

    public function down()
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropColumn('PadNumber');
        });
    }
}
