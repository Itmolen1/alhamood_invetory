<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddManualPaymentColumnsToSupplierAdvancesTable extends Migration
{
    public function up()
    {
        Schema::table('supplier_advances', function (Blueprint $table) {
            $table->decimal('spentBalance',10,2)->default('0');
            $table->decimal('remainingBalance',10,2)->default('0');
            $table->boolean('IsSpent')->default(0);
            $table->boolean('IsPartialSpent')->default(0);
        });
    }

    public function down()
    {
        Schema::table('supplier_advances', function (Blueprint $table) {
            $table->dropColumn('spentBalance');
            $table->dropColumn('remainingBalance');
            $table->dropColumn('IsSpent');
            $table->dropColumn('IsPartialSpent');
        });
    }
}
