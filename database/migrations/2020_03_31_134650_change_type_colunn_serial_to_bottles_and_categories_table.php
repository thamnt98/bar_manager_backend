<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTypeColunnSerialToBottlesAndCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bottle_categories', function (Blueprint $table) {
            $table->integer('serial')->charset('')->collation('')->change();
        });
        Schema::table('bottles', static function (Blueprint $table) {
            $table->integer('serial')->charset('')->collation('')->change();
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
            $table->string('serial', 255)->change();
        });
        Schema::table('bottles', function (Blueprint $table) {
            $table->string('serial', 255)->change();
        });
    }
}
