<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('totalAmount','10','2')->default(0);
            $table->decimal('paidAmount','10','2')->default(0);
            $table->string('amountInWords')->nullable();
            $table->unsignedBigInteger('supplier_id')->default(0)->index();
            $table->unsignedBigInteger('user_id')->default(0)->index();
            $table->unsignedBigInteger('company_id')->default(0)->index();
            $table->unsignedBigInteger('bank_id')->default(0)->index();
            $table->string('accountNumber')->nullable();
            $table->date('transferDate')->default(date('Y-m-d'));
            $table->string('payment_type')->nullable();
            $table->string('referenceNumber')->nullable();
            $table->string('receiverName')->nullable();
            $table->string('receiptNumber')->nullable();
            $table->text('Description')->nullable();
            $table->text('updateDescription')->nullable();
            $table->date('supplierPaymentDate')->default(date('Y-m-d'));
            $table->date('createdDate')->default(date('Y-m-d'));
            $table->boolean('isPushed')->default(false);
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
        Schema::dropIfExists('supplier_payments');
    }
}
