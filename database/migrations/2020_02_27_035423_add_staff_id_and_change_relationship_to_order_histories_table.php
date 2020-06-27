<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStaffIdAndChangeRelationshipToOrderHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->dropForeign('order_histories_cast_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_histories', function (Blueprint $table) {
            $table->dropColumn('staff_id');
            $table->foreign('cast_id')->references('id')->on('casts')->onDelete('cascade');
        });
    }
}
