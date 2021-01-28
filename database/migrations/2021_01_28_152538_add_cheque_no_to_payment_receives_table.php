<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChequeNoToPaymentReceivesTable extends Migration
{
    public function up()
    {
        Schema::table('payment_receives', function (Blueprint $table) {
            $table->text('ChequeNumber')->nullable();
        });
    }

    public function down()
    {
        Schema::table('payment_receives', function (Blueprint $table) {
            $table->dropColumn('ChequeNumber');
        });
    }
}
