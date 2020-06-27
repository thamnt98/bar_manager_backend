<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRemainColumnToKeepBottle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('keep_bottles', function (Blueprint $table) {
            $table->addColumn('integer', 'remain')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('keep_bottles', function (Blueprint $table) {
            $table->dropColumn('remain');
        });
    }
}
