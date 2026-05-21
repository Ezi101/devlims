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
        Schema::create('p_t_r_s', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id')->unsigned();
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->string('ptr_no');
            $table->string('sample_id')->nullable();
            $table->string('batch_no')->nullable();
            $table->string('contract_no')->nullable();
            $table->string('supplier_id')->nullable();
            $table->string('r_stock_id')->nullable();
            $table->string('test_id')->nullable();
            $table->string('test_name')->nullable();
            $table->string('test_specifications')->nullable();
            $table->dateTime('reported_datetime')->nullable();
            $table->dateTime('print_date')->nullable();
            $table->dateTime('a_p_date')->nullable();
            $table->enum('status', ['approved', 'pending', 'rejected'])->nullable();
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
        Schema::dropIfExists('p_t_r_s');
    }
};
