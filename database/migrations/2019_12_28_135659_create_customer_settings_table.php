<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->json('label_order_setting');
            $table->integer('record_per_visit_page')->default(100);
            $table->integer('record_per_customer_page')->default(100);
            $table->unsignedBigInteger('bar_id');
            $table->bigInteger('keep_bottle_day_limit');
            $table->string('sort_by',255);
            $table->boolean('is_trash')->default(false);
            $table->foreign('bar_id')->references('id')->on('bars')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_settings');
    }
}
