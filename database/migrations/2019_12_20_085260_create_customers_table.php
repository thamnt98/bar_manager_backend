<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name',255);
            $table->string('transcribed_name',255)->nullable();
            $table->string('icon',255)->nullable();
            $table->integer('age')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('home_town',255)->nullable();
            $table->string('address',255)->nullable();
            $table->enum('blood_type', ['A', 'B', 'AB', 'O'])->nullable();
            $table->string('character',255)->nullable();
            $table->string('feature',255)->nullable();
            $table->string('company_name',255)->nullable();
            $table->string('department',255)->nullable();
            $table->string('position',255)->nullable();
            $table->string('job',255)->nullable();
            $table->string('post-no',255)->nullable();
            $table->string('company_tower',255)->nullable();
            $table->string('email',255)->nullable();
            $table->string('phone_number',255)->nullable();
            $table->string('line_account_id',255)->nullable();
            $table->string('day_of_week_can_be_contact',255)->nullable();
            $table->string('favorite_bottle',255)->nullable();
            $table->bigInteger('favorite_cast_id')->nullable();
            $table->bigInteger('favorite_cast_help_id')->nullable();
            $table->bigInteger('favorite_waiter_account_id')->nullable();
            $table->boolean('come_alone')->default(false);
            $table->boolean('has_smoke')->default(false);
            $table->string('favorite_cigarette',255)->nullable();
            $table->boolean('has_marriage')->default(false);
            $table->boolean('has_child')->default(false);
            $table->boolean('has_lover')->default(false);
            $table->longText('favorite_song')->nullable();
            $table->longText('favorite_singer')->nullable();
            $table->longText('hobbies')->nullable();
            $table->longText('skills')->nullable();
            $table->string('day_offs',255)->nullable();
            $table->string('favorite_brand',255)->nullable();
            $table->boolean('play_golf')->default(false);
            $table->bigInteger('in_charge_cast_id')->nullable();
            $table->bigInteger('favorite_rank')->default(0);
            $table->bigInteger('income_rank')->default(0);
            $table->boolean('is_trash')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
}
