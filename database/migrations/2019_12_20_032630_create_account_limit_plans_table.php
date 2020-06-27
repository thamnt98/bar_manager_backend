<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountLimitPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_limit_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type', ['limited', 'free', 'unlimited']);
            $table->bigInteger('customer_limit')->nullable();
            $table->string('name', 255)->nullable();
            $table->boolean('is_trash')->default(false);
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
        Schema::dropIfExists('account_limit_plans');
    }
}
