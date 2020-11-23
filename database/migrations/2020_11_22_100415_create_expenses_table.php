<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('expenseNumber')->nullable();
            $table->unsignedBigInteger('user_id')->default('0')->index();
            $table->unsignedBigInteger('company_id')->default('0')->index();
            $table->unsignedBigInteger('employee_id')->default('0')->index();
            $table->unsignedBigInteger('supplier_id')->default(0);
            $table->date('expenseDate')->default(date('Y-m-d'));
            $table->string('referenceNumber')->nullable();
            $table->decimal('Total',10,2)->default('0');
            $table->decimal('subTotal',10,2)->default('0');
            $table->decimal('totalVat',10,2)->default('0');
            $table->decimal('grandTotal',10,2)->default('0');
            $table->decimal('paidBalance',10,2)->default('0');
            $table->decimal('remainingBalance',10,2)->default('0');
            $table->text('Description')->nullable();
            $table->text('updateDescription')->nullable();
            $table->text('termsAndCondition')->nullable();
            $table->text('supplierNote')->nullable();
            $table->boolean('isApprove')->default(true);
            $table->boolean('isDelay')->default(true);
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
        Schema::dropIfExists('expenses');
    }
}
