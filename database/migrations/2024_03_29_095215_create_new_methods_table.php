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
        Schema::create('new_methods', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id')->unsigned();
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->string('method_no')->nullable();
            $table->string('method_name')->nullable();
            $table->string('method_description')->nullable();
            $table->string('sample_id')->nullable();
            $table->string('batch_no')->nullable();
            $table->string('generic_name_id')->nullable();
            $table->string('ptr_id')->nullable();
            $table->string('ptr_no')->nullable();
            $table->string('test_id')->nullable();
            $table->string('test_name')->nullable();
            $table->string('test_specifications')->nullable();
            $table->string('analyst_id')->nullable();
            $table->string('test_analyst')->nullable();
            $table->string('created_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->string('approved_at')->nullable();
            $table->dateTime('reported_datetime')->nullable();
            $table->dateTime('print_date')->nullable();
            $table->enum('status', ['approved', 'pending', 'rejected'])->nullable();
            $table->json('files')->nullable();
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
        Schema::dropIfExists('new_methods');
    }
};
