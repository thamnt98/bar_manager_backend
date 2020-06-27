<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNullableForCreateStaffAccountInAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('requested_upgrade_plan_id')->nullable()->change();
            $table->unsignedBigInteger('limit_plan_id')->nullable()->change();
            $table->bigInteger("creator_id")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('requested_upgrade_plan_id')->change();
            $table->unsignedBigInteger('limit_plan_id')->change();
            $table->dropColumn("creator_id");
        });
    }
}
