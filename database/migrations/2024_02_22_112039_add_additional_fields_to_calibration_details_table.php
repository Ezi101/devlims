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
        Schema::table('calibration_details', function (Blueprint $table) {
            $table->date('calibration_date');
            $table->date('guaranteed_date')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedTinyInteger('calibration_frequency');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('calibration_details', function (Blueprint $table) {
            $table->dropColumn('calibration_date');
            $table->dropColumn('guaranteed_date');
            $table->dropColumn('remarks');
            $table->dropColumn('calibration_frequency');
        });
    }
};
