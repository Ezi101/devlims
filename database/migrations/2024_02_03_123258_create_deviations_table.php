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
        Schema::create('deviations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('type')->nullable();
            $table->text('description')->nullable();
            $table->date('deviation_date')->default(now());
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
        Schema::dropIfExists('deviations');
    }
};
