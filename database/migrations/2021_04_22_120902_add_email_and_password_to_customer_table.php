<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmailAndPasswordToCustomerTable extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('login_email')->nullable();
            $table->string('password')->nullable();
            $table->dateTime('password_last_updated');
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('login_email');
            $table->dropColumn('password');
            $table->dropColumn('password_last_updated');
        });
    }
}
