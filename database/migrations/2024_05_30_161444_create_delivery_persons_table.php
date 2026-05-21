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
        Schema::create('delivery_persons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('cnic')->unique();
            $table->string('phone');
            $table->string('picture')->nullable();
            $table->integer('business_id')->unsigned();
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->unsignedInteger('product_id')->nullable();
            $table->unsignedInteger('transaction_id')->nullable();
            $table->unsignedInteger('supplier_id')->nullable();
            $table->unsignedInteger('batch_no')->nullable();
            $table->unsignedInteger('contract_no')->nullable();
            $table->string('delivery_date_time')->nullable();
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
        Schema::dropIfExists('delivery_persons');
    }
};
