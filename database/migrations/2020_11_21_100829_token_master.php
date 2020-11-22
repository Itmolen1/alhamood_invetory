<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TokenMaster extends Migration
{
    public function up()
    {
        Schema::create('token_master', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->unsignedBigInteger('user_id')->default('0')->index();
            $table->string('device_token')->nullable();
            $table->string('device_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        //
    }
}
