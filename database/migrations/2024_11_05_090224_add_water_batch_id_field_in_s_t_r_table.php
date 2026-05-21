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
        Schema::table('s_t_r', function (Blueprint $table) {
            $table->unsignedBigInteger('w_batch_id')->nullable()->after('batch_no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('s_t_r', function (Blueprint $table) {
            $table->dropColumn('w_batch_id');
        });
    }
};
