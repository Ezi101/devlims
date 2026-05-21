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
        Schema::create('spillages', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id')->nullable();
            $table->unsignedInteger('chemical_id')->nullable();
            $table->unsignedInteger('standard_id')->nullable();
            $table->text('spillage_remarks')->nullable();
            $table->text('spillage_quantity')->nullable();
            $table->dateTime('spillage_date_time')->nullable();
            $table->unsignedInteger('reported_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('spillages');
    }
};
