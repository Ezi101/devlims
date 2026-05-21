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
        Schema::create('calibration_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('instruments');
            $table->string('calibrator_name');
            $table->string('calibrator_cnic');
            $table->string('calibrator_mobile');
            $table->enum('calibration_type', ['annual', 'non-annual']);
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
        Schema::dropIfExists('calibration_details');
    }
};
