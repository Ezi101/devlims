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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id')->unsigned();
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->integer('user_id')->unsigned();
            $table->string('number',255)->nullable();
            $table->string('t_quantity',155)->nullable();
            $table->string('dosage_form',155)->nullable();
            $table->string('t_installment',155)->nullable();
            $table->string('1st_installment',155)->nullable();
            $table->string('2nd_installment',155)->nullable();
            $table->string('3rd_installment',155)->nullable();
            $table->string('4rt_installment',155)->nullable();
            $table->string('5th_installment',155)->nullable();
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
        Schema::dropIfExists('contracts');
    }
};
