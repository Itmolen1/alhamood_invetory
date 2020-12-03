<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierAdvancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_advances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('supplier_id')->default(0);
            $table->string('receiptNumber')->nullable();
            $table->string('paymentType')->nullable();
            $table->decimal('Amount',10,2)->default('0');
            $table->string('sumOf')->nullable();
            $table->string('receiverName')->nullable();
            $table->text('Description')->nullable();
            $table->text('updateDescription')->nullable();
            $table->unsignedBigInteger('user_id')->default(0);
            $table->unsignedBigInteger('company_id')->default(0);
            $table->unsignedBigInteger('bank_id')->default(0);
            $table->string('accountNumber')->nullable();
            $table->date('TransferDate')->default(now());
            $table->date('registerDate')->default(now());
            $table->timestamp('createdDate')->useCurrent();
            $table->boolean('isActive')->default(true);
            $table->boolean('isPushed')->default(false);
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
        Schema::dropIfExists('supplier_advances');
    }
}
