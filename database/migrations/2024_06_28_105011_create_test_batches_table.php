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
        Schema::create('test_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id'); // Add this line
            $table->foreignId('task_id'); // Add this line
            $table->string('test'); // Add this line
            $table->foreignId('sample_id'); // Add this line
            $table->foreignId('sample_reading_id'); // Add this line
            $table->foreignId('test_id'); // Add this line
            $table->foreignId('analyst_id'); // Add this line
            $table->string('results')->nullable();
            $table->string('comply')->nullable();
            $table->string('specifications')->nullable();
            $table->string('raw_data')->nullable();
            $table->enum('status', ['draft', 'fraword'])->nullable();
            $table->timestamps();
        
            // Foreign key constraints
            // $table->foreign('test_id')->references('id')->on('pjt_project_tasks')->onDelete('cascade');
           
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('test_batches');
    }
};
