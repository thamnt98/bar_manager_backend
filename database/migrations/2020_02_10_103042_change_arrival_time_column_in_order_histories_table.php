<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeArrivalTimeColumnInOrderHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_histories', function (Blueprint $table) {
            $table->renameColumn('arrival_time', 'arrival_at');
            $table->renameColumn('leave_time', 'leave_at');
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
            $table->renameColumn('arrival_at', 'arrival_time');
            $table->renameColumn('leave_at', 'leave_time');
        });
    }
}
