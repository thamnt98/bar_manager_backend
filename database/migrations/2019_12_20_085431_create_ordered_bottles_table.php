<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderedBottlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ordered_bottles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->double('price');
            $table->boolean('emptied');
            $table->unsignedBigInteger('bottle_id');
            $table->unsignedBigInteger('order_id');
            $table->boolean('is_trash')->default(false);
            $table->timestamps();
            $table->foreign('bottle_id')->references('id')->on('bottles')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('order_histories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ordered_bottles');
    }
}
