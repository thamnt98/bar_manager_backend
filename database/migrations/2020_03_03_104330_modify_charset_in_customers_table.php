<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyCharsetInCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('name', 255)->collation('utf8mb4_general_ci')->change();
            $table->string('company_name', 255)->collation('utf8mb4_general_ci')->change();
            $table->string('furigana_name', 255)->collation('utf8mb4_general_ci')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('name', 255)->collation('utf8mb4_unicode_ci')->change();
            $table->string('company_name', 255)->collation('utf8mb4_unicode_ci')->change();
            $table->string('furigana_name', 255)->collation('utf8mb4_unicode_ci')->change();
        });
    }
}
