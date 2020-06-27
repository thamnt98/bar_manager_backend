<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type', ['free', 'shimei' , 'honnaishimei', 'douhan'])->nullable();
            $table->timestamp('arrival_time');
            $table->double('total_income')->nullable();
            $table->integer('stayed_hour');
            $table->longText('note')->nullable();
            $table->unsignedBigInteger('cast_id');
            $table->unsignedBigInteger('cast_help_id')->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->boolean('is_trash')->default(false);
            $table->timestamps();
            $table->foreign('cast_id')->references('id')->on('casts')->onDelete('cascade');
            $table->foreign('cast_help_id')->references('id')->on('casts')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_histories');
    }
}
