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
        Schema::create('oos', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('product_name');
            $table->text('reason')->nullable();
            $table->boolean('resolved')->default(false);
            $table->date('reported_at')->default(now());
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
        Schema::dropIfExists('oos');
    }
};
