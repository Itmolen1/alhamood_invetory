<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinancerTable extends Migration
{
    public function up()
    {
        Schema::create('financer', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('Name');
            $table->string('Mobile')->nullable();
            $table->text('Description')->nullable();
            $table->unsignedBigInteger('user_id')->default(0);
            $table->unsignedBigInteger('company_id')->default(0);
            $table->decimal('openingBalance','10','2')->default(0);
            $table->date('openingBalanceAsOfDate')->default(date('Y-m-d'));
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('financer');
    }
}
