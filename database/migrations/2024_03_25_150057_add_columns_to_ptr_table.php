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
            $table->string('approved_by')->nullable();
            $table->string('rejected_by')->nullable();
            $table->text('signature')->nullable();
            $table->text('remarks')->nullable();
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
            $table->dropColumn('approved_by');
            $table->dropColumn('rejected_by');
            $table->dropColumn('signature');
            $table->dropColumn('remarks');
        });
    }
};
