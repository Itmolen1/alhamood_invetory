<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanMastersTable extends Migration
{
    public function up()
    {
        Schema::create('loan_masters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('totalAmount',10,2)->default('0');
            $table->string('amountInWords')->nullable();
            $table->string('referenceNumber')->nullable();
            $table->boolean('loanType')->default(0)->comment('0-Outward 1-Inward');
            $table->date('loanDate')->default(date('Y-m-d'));
            $table->boolean('isPushed')->default(0);
            $table->unsignedBigInteger('customer_id')->default('0')->index();
            $table->unsignedBigInteger('financer_id')->default('0')->index();
            $table->unsignedBigInteger('employee_id')->default('0')->index();
            $table->unsignedBigInteger('user_id')->default('0')->index();
            $table->unsignedBigInteger('company_id')->default('0')->index();
            $table->text('Description')->nullable();
            $table->text('updateDescription')->nullable();
            $table->boolean('outward_isPaid')->default(0);
            $table->boolean('outward_isPartialPaid')->default(0);
            $table->decimal('outward_PaidBalance',10,2)->default('0');
            $table->decimal('outward_RemainingBalance',10,2)->default('0');
            $table->boolean('inward_isPaid')->default(0);
            $table->boolean('inward_isPartialPaid')->default(0);
            $table->decimal('inward_PaidBalance',10,2)->default('0');
            $table->decimal('inward_RemainingBalance',10,2)->default('0');
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
        Schema::dropIfExists('loan_masters');
    }
}
