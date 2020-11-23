<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpenseDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expense_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('expense_id')->default(0);
            $table->unsignedBigInteger('user_id')->default(0);
            $table->unsignedBigInteger('company_id')->default(0);
            $table->unsignedBigInteger('expense_category_id')->default(0);
            $table->date('createdDate')->default(date('Y-m-d'));
            $table->date('expenseDate')->default(date('Y-m-d'));
            $table->string('PadNumber')->nullable();
            $table->text('Description')->nullable();
            $table->decimal('Total',10,2)->default('0');
            $table->decimal('VAT',10,2)->default('0');
            $table->decimal('rowVatAmount',10,2)->default('0');
            $table->decimal('rowSubTotal',10,2)->default('0');
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
        Schema::dropIfExists('expense_details');
    }
}
