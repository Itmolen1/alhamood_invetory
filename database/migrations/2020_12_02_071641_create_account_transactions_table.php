<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('Credit',10,2)->default('0');
            $table->decimal('Debit',10,2)->default('0');
            $table->decimal('Differentiate',10,2)->default('0');
            $table->string('referenceNumber')->nullable();
            $table->unsignedBigInteger('user_id')->default('0')->index();
            $table->unsignedBigInteger('customer_id')->default('0')->index();
            $table->unsignedBigInteger('supplier_id')->default('0')->index();
            $table->unsignedBigInteger('employee_id')->default('0')->index();
            $table->unsignedBigInteger('company_id')->default('0')->index();
            $table->text('Description')->nullable();
            $table->text('updateDescription')->nullable();
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
        Schema::dropIfExists('account_transactions');
    }
}
