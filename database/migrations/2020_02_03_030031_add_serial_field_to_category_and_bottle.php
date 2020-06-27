<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSerialFieldToCategoryAndBottle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bottle_categories', function (Blueprint $table) {
            $table->string('serial', 255)->nullable();
        });
        Schema::table('bottles', function (Blueprint $table) {
            $table->string('serial', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bottle_categories', function (Blueprint $table) {
            $table->dropColumn('serial');
        });
        Schema::table('bottles', function (Blueprint $table) {
            $table->dropColumn('serial');
        });
    }
}
