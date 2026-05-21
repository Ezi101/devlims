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
        Schema::create('source_customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('cnic');
            $table->string('phone');
            $table->string('city');
            $table->string('picture')->nullable();
            $table->integer('business_id')->unsigned();
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->unsignedInteger('product_id')->nullable();
            $table->unsignedInteger('transaction_id')->nullable();
            $table->unsignedInteger('contract_no')->nullable();
            $table->string('purchase_date_time')->nullable();
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
        Schema::dropIfExists('source_customers');
    }
};
