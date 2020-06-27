<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('password', 255);
            $table->string('name', 255)->nullable();
            $table->string('display_name', 255)->nullable();
            $table->string('email', 255)->unique();
            $table->boolean('is_admin')->default(false);
            $table->rememberToken();
            $table->boolean('is_requested_upgrade_plan')->default(false);
            $table->string('invite_code', 255)->nullable();
            $table->bigInteger('invited_account')->nullable()->default(0);
            $table->unsignedBigInteger('requested_upgrade_plan_id');
            $table->unsignedBigInteger('limit_plan_id');
            $table->boolean('is_trash')->default(false);
            $table->foreign('requested_upgrade_plan_id')->references('id')->on('account_limit_plans')->onDelete('cascade');
            $table->foreign('limit_plan_id')->references('id')->on('account_limit_plans')->onDelete('cascade');
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
        Schema::dropIfExists('accounts');
    }
}
