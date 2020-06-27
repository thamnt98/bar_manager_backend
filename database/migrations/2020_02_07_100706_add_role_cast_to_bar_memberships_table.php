<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRoleCastToBarMembershipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bar_memberships', function (Blueprint $table) {
            $table->enum('role', ['owner', 'manager', 'staff', 'cast'])->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bar_memberships', function (Blueprint $table) {
            $table->enum('role', ['owner', 'manager', 'staff'])->change();
        });
    }
}
