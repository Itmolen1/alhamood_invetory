<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerIdToSaleDetailsTable extends Migration
{
    public function up()
    {
        Schema::table('sale_details', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->default(0)->index();
        });
    }

    public function down()
    {
        Schema::table('sale_details', function (Blueprint $table) {
            $table->dropColumn('customer_id');
        });
    }
}
