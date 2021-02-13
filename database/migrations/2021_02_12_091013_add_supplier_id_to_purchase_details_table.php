<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSupplierIdToPurchaseDetailsTable extends Migration
{
    public function up()
    {
        Schema::table('purchase_details', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id')->default(0)->index();
        });
    }

    public function down()
    {
        Schema::table('purchase_details', function (Blueprint $table) {
            $table->dropColumn('supplier_id');
        });
    }
}
