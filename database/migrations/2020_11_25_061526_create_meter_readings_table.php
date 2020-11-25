<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeterReadingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meter_readings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('readingDate')->default(date('Y-m-d'));
            $table->decimal('startPad',10,2)->default('0');
            $table->decimal('endPad',10,2)->default('0');
            $table->decimal('totalPadSale',10,2)->default('0');
            $table->decimal('totalMeterSale',10,2)->default('0');
            $table->decimal('saleDifference',10,2)->default('0');
            $table->unsignedBigInteger('user_id')->default('0')->index();
            $table->unsignedBigInteger('employee_id')->default('0')->index();
            $table->unsignedBigInteger('company_id')->default('0')->index();
            $table->text('Description')->nullable();
            $table->date('createdDate')->default(date('Y-m-d'));
            $table->boolean('isActive')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meter_readings');
    }
}
