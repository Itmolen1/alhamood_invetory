<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('SaleNumber')->nullable();
            $table->unsignedBigInteger('user_id')->default('0')->index();
            $table->unsignedBigInteger('company_id')->default('0')->index();
            $table->unsignedBigInteger('customer_id')->default(0);
            $table->date('SaleDate')->default(date('Y-m-d'));
            $table->date('DueDate')->default(date('Y-m-d'));
            $table->string('referenceNumber')->nullable();
            $table->decimal('Total',10,2)->default('0');
            $table->decimal('subTotal',10,2)->default('0');
            $table->decimal('totalVat',10,2)->default('0');
            $table->decimal('grandTotal',10,2)->default('0');
            $table->decimal('paidBalance',10,2)->default('0');
            $table->decimal('remainingBalance',10,2)->default('0');
            $table->text('Description')->nullable();
            $table->text('UpdateDescription')->nullable();
            $table->text('TermsAndCondition')->nullable();
            $table->text('supplierNote')->nullable();
            $table->boolean('IsPaid')->default(true);
            $table->boolean('IsPartialPaid')->default(true);
            $table->boolean('IsReturn')->default(true);
            $table->boolean('IsPartialReturn')->default(true);
            $table->boolean('IsNeedStampOrSignature')->default(true);
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
        Schema::dropIfExists('sales');
    }
}
