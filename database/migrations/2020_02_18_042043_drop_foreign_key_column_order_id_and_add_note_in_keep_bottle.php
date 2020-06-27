<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropForeignKeyColumnOrderIdAndAddNoteInKeepBottle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('keep_bottles', function (Blueprint $table) {
            $table->string('note', 255)->nullable();
            $table->dropForeign('keep_bottles_order_id_foreign');
            $table->renameColumn('order_id', 'customer_id');
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
            $table->dropColumn('note');
            $table->renameColumn('customer_id', 'order_id');
            $table->foreign('order_id')->references('id')->on('order_histories')->onDelete('cascade');
        });
    }
}
