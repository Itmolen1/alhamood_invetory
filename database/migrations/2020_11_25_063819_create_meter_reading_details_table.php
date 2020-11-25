<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeterReadingDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meter_reading_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('startReading',10,2)->default('0');
            $table->decimal('endReading',10,2)->default('0');
            $table->decimal('netReading',10,2)->default('0');
            $table->decimal('Purchases',10,2)->default('0');
            $table->decimal('Sales',10,2)->default('0');
            $table->unsignedBigInteger('user_id')->default('0')->index();
            $table->unsignedBigInteger('meter_reading_id')->default('0')->index();
            $table->unsignedBigInteger('company_id')->default('0')->index();
            $table->unsignedBigInteger('meter_reader_id')->default(0)->index();
            $table->text('Description')->nullable();
            $table->date('meterDate')->default(date('Y-m-d'));
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
        Schema::dropIfExists('meter_reading_details');
    }
}
