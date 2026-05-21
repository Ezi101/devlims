<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('p_t_r_s', function (Blueprint $table) {
            $table->string('forward')->nullable()->after('rejector_role');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('p_t_r_s', function (Blueprint $table) {
            $table->dropColumn('forward');
        });
    }
};
