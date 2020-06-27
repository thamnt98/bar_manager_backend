<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDebitHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('debit_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('paid_money');
            $table->date('pay_day');
            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')->references('id')->on('order_histories')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('debit_histories');
    }
}
