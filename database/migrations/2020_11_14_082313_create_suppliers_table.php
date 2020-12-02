<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('Name');
            $table->string('Representative')->nullable();
            $table->string('TRNNumber')->nullable();
            $table->string('fileUpload')->nullable();
            $table->string('Phone')->nullable();
            $table->string('Mobile')->nullable();
            $table->string('Email')->nullable();
            $table->string('Address')->nullable();
            $table->string('postCode')->nullable();
            $table->date('registrationDate')->default(now());
            $table->text('Description')->nullable();
            $table->text('updateDescription')->nullable();
            $table->unsignedBigInteger('user_id')->default(0);
            $table->unsignedBigInteger('payment_type_id')->default(0);
            $table->unsignedBigInteger('company_type_id')->default(0);
            $table->unsignedBigInteger('payment_term_id')->default(0);
            $table->unsignedBigInteger('company_id')->default(0);
            $table->unsignedBigInteger('region_id')->default(0);
            $table->timestamp('createdDate')->useCurrent();
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
        Schema::dropIfExists('suppliers');
    }
}
