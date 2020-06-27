<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBarIdAndDefaultPriceInKeepBottleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('keep_bottles', function (Blueprint $table) {
            $table->unsignedBigInteger('bar_id');
            $table->float('price')->default(0)->change();
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
            $table->dropColumn('bar_id');
            $table->float('price')->change();
        });
    }
}
