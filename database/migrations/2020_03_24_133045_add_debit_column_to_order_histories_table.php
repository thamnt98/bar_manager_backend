<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDebitColumnToOrderHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_histories', function (Blueprint $table) {
            $table->enum('pay_method', ['card', 'cash', 'debit'])->default('cash');
            $table->double('debt')->nullable();
            $table->date('pay_day')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_histories', function (Blueprint $table) {
            $table->dropColumn('pay_method');
            $table->dropColumn('debt');
            $table->dropColumn('pay_day');
        });
    }
}
