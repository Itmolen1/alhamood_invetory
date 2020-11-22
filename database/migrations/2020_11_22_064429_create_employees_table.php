<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('Name')->nullable();
            $table->string('Mobile')->nullable();
            $table->string('emergencyContactNumber')->nullable();
            $table->string('identityNumber')->nullable();
            $table->string('passportNumber')->nullable();
            $table->string('Address')->nullable();
            $table->string('driverLicenceNumber')->nullable();
            $table->string('driverLicenceExpiry')->nullable();
            $table->string('startOfJob')->nullable();
            $table->dateTime('DOB')->default(date('Y-m-d'));
            $table->unsignedBigInteger('user_id')->default('0')->index();
            $table->unsignedBigInteger('marital_id')->default('0')->index();
            $table->unsignedBigInteger('designation_id')->default('0')->index();
            $table->unsignedBigInteger('referenceEmployee_id')->default('0')->index();
            $table->unsignedBigInteger('shift_id')->default('0')->index();
            $table->unsignedBigInteger('department_id')->default('0')->index();
            $table->unsignedBigInteger('region_id')->default('0')->index();
            $table->unsignedBigInteger('gender_id')->default('0')->index();
            $table->unsignedBigInteger('company_id')->default('0')->index();
            $table->date('createdDate')->default(date('Y-m-d'));
            $table->text('Description')->nullable();
            $table->text('UpdateDescription')->nullable();
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
        Schema::dropIfExists('employees');
    }
}
