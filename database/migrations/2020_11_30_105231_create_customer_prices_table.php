<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_prices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('Rate',10,2)->default('0');
            $table->decimal('VAT',10,2)->default('0');
            $table->decimal('customerLimit')->nullable();
            $table->unsignedBigInteger('user_id')->default('0')->index();
            $table->unsignedBigInteger('customer_id')->default('0')->index();
            $table->unsignedBigInteger('company_id')->default('0')->index();
            $table->text('Description')->nullable();
            $table->text('updateDescription')->nullable();
            $table->date('pricesDate')->default(date('Y-m-d'));
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
        Schema::dropIfExists('customer_prices');
    }
}
