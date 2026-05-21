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
        Schema::create('test_approveds', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id')->unsigned();
            $table->unsignedBigInteger('test_id');
            $table->unsignedBigInteger('approved_by');
            $table->enum('status',['approved','rejected'])->nullable();
            $table->string('remarks')->nullable();
            $table->string('date');
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
        Schema::dropIfExists('test_approveds');
    }
};
