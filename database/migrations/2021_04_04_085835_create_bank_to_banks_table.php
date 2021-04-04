<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankToBanksTable extends Migration
{
    public function up()
    {
        Schema::create('bank_to_banks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('Amount',10,2)->default('0');
            $table->unsignedBigInteger('from_bank_id')->default(0);
            $table->unsignedBigInteger('to_bank_id')->default(0);
            $table->string('Reference')->nullable();
            $table->date('depositDate')->default(date('Y-m-d'));
            $table->unsignedBigInteger('user_id')->default('0')->index();
            $table->unsignedBigInteger('company_id')->default('0')->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bank_to_banks');
    }
}
