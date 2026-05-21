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
        Schema::create('utilizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('instruments');
            $table->integer('performed_by');
            $table->dateTime('utilization_start_time');
            $table->dateTime('utilization_end_time');
            $table->enum('apparatus_status', ['okay', 'not_okay']);
            $table->string('sample_name');
            $table->string('sample_number');
            $table->integer('rpm');
            $table->string('apparatus_used_name');
            $table->dateTime('cleaning_start_time')->nullable();
            $table->dateTime('cleaning_end_time')->nullable();
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
        Schema::dropIfExists('utilizations');
    }
};
