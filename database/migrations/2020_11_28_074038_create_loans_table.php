<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('loanTo')->nullable();
            $table->decimal('payLoan',10,2)->default('0');
            $table->string('loanInWords')->nullable();
            $table->string('voucherNumber')->nullable();
            $table->decimal('remainingLoan',10,2)->default('0');
            $table->unsignedBigInteger('user_id')->default('0')->index();
            $table->unsignedBigInteger('customer_id')->default('0')->index();
            $table->unsignedBigInteger('employee_id')->default('0')->index();
            $table->unsignedBigInteger('company_id')->default('0')->index();
            $table->text('Description')->nullable();
            $table->text('updateDescription')->nullable();
            $table->date('loanDate')->default(date('Y-m-d'));
            $table->date('createdDate')->default(date('Y-m-d'));
            $table->boolean('isPay')->default(true);
            $table->boolean('isReturn')->default(true);
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
        Schema::dropIfExists('loans');
    }
}
