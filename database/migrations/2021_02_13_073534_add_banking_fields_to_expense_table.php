<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBankingFieldsToExpenseTable extends Migration
{
    public function up()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('bank_id')->default(0)->index();
            $table->string('accountNumber')->nullable();
            $table->date('transferDate')->default(date('Y-m-d'));
            $table->string('payment_type')->nullable();
            $table->text('ChequeNumber')->nullable();
        });
    }

    public function down()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('bank_id');
            $table->dropColumn('accountNumber');
            $table->dropColumn('transferDate');
            $table->dropColumn('payment_type');
            $table->dropColumn('ChequeNumber');
        });
    }
}
