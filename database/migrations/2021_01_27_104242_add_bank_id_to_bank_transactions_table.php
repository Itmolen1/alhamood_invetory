<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBankIdToBankTransactionsTable extends Migration
{
    public function up()
    {
        Schema::table('bank_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('bank_id')->default('0')->index();
            $table->text('updateDescription')->nullable();
        });
    }

    public function down()
    {
        Schema::table('bank_transactions', function (Blueprint $table) {
            $table->dropColumn('bank_id');
            $table->dropColumn('updateDescription');
        });
    }
}
