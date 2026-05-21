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
        Schema::create('contract_monthly_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id');
            $table->unsignedBigInteger('business_id');
            $table->decimal('contract_quantity', 22, 4)->default(0);
            $table->decimal('received_quantity', 22, 4)->default(0);
            $table->integer('month');
            $table->integer('year');
            $table->timestamps();

            // Foreign key constraint (optional, lekin behtar hai)
            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contract_monthly_logs');
    }
};
