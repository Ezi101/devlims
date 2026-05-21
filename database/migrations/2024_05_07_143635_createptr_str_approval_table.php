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
        Schema::create('ptr_str_approval', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id')->unsigned();
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->string('ptr/str_no');
            $table->unsignedInteger('remark_by')->nullable();
            $table->enum('remark_status', ['approved', 'rejected'])->nullable();
            $table->string('remark_date_time')->nullable();
            $table->unsignedInteger('remark_to')->nullable();
            $table->string('remark')->nullable();
           
            
           
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
        Schema::dropIfExists('ptr_str_approval');
    }
};
