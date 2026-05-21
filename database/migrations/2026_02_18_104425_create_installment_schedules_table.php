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
        Schema::create('installment_schedules', function (Blueprint $table) {
            $table->id();
            $table->integer('transaction_id')->index();
            $table->decimal('amount', 22, 4)->default(0);
            $table->date('due_date');
            $table->string('status')->default('pending');
            $table->text('description')->nullable();
            $table->integer('business_id')->nullable();
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
        Schema::dropIfExists('installment_schedules');
    }
};
