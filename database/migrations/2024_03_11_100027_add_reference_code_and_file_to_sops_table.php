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
        Schema::table('s_o_p_s', function (Blueprint $table) {
            $table->string('reference_code')->nullable()->after('description');
            $table->string('file')->nullable()->after('reference_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('s_o_p_s', function (Blueprint $table) {
            $table->dropColumn('reference_code');
            $table->dropColumn('file');
        });
    }
};
