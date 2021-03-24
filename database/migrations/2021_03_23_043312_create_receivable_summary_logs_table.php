<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceivableSummaryLogsTable extends Migration
{
    public function up()
    {
        Schema::create('receivable_summary_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->default(0);
            $table->unsignedBigInteger('customer_id')->default(0);
            $table->decimal('BalanceAmount','10','2')->default(0);
            $table->date('RecordDate')->default(date('Y-m-d'));
            $table->text('Description')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('receivable_summary_logs');
    }
}
