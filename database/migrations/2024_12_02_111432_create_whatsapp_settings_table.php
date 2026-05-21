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
        Schema::create('whatsapp_settings', function (Blueprint $table) {
            $table->id();
            $table->string('instance_id');
            $table->string('access_token');
            $table->json('recipients'); // Array of numbers
            $table->json('modules');   // Selected modules
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
        Schema::dropIfExists('whatsapp_settings');
    }
};
