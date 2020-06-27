<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderSettingToCustomerSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_settings', function (Blueprint $table) {
            $table->dropForeign(['bar_id']);
            $table->string('sort_by', 50)->nullable()->change();
            $table->renameColumn('sort_by', 'order_by');
            $table->string('order_name', 50)->nullable();
            $table->json('label_order_setting')->nullable()->change();
            $table->unsignedBigInteger('bar_id')->nullable()->change();
            $table->bigInteger('keep_bottle_day_limit')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_settings', function (Blueprint $table) {
            $table->foreign('bar_id')->references('id')->on('bars')->onDelete('cascade');
            $table->string('order_by', 255)->nullable(false)->change();
            $table->renameColumn('order_by', 'sort_by');
            $table->dropColumn('order_name');
            $table->json('label_order_setting')->nullable(false)->change();
            $table->bigInteger('keep_bottle_day_limit')->nullable(false)->change();
        });
    }
}
