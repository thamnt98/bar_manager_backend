<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFriendshipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('friendships', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('friend_name',255);
            $table->unsignedBigInteger('friend_type_id');
            $table->unsignedBigInteger('customer_id');
            $table->boolean('is_trash')->default(false);
            $table->timestamps();
            $table->foreign('friend_type_id')->references('id')->on('friend_types')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('friendships');
    }
}
