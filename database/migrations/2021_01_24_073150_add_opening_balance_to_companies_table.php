<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOpeningBalanceToCompaniesTable extends Migration
{
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->decimal('openingBalance','10','2')->default(0);
            $table->date('openingBalanceAsOfDate')->default(date('Y-m-d'));
        });
    }


    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('openingBalance');
            $table->dropColumn('openingBalanceAsOfDate');
        });
    }
}
