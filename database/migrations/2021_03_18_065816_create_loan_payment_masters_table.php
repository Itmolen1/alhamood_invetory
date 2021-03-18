<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanPaymentMastersTable extends Migration
{
    public function up()
    {
        Schema::create('loan_payment_masters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('amountPaid',10,2)->default('0');
            $table->string('referenceNumber')->nullable();
            $table->boolean('loanType')->default(0)->comment('0-Outward 1-Inward');
            $table->date('paymentDate')->default(date('Y-m-d'));
            $table->unsignedBigInteger('loan_master_id')->default('0')->index();
            $table->unsignedBigInteger('user_id')->default('0')->index();
            $table->unsignedBigInteger('company_id')->default('0')->index();
            $table->text('Description')->nullable();
            $table->text('updateDescription')->nullable();
            $table->string('payment_type')->nullable();
            $table->unsignedBigInteger('bank_id')->default('0')->index();
            $table->string('accountNumber')->nullable();
            $table->string('ChequeNumber')->nullable();
            $table->date('transferDate')->default(date('Y-m-d'));
            $table->boolean('isActive')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('loan_payment_masters');
    }
}
