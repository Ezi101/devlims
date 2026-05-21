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
        Schema::create('product_id_replacement_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('old_product_id')->index();
            $table->string('old_product_name')->nullable();
            $table->integer('new_product_id')->index();
            $table->string('new_product_name')->nullable();
            $table->text('update_details')->nullable();

            $table->integer('updated_by')->unsigned();

            $table->timestamps();

            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_id_replacement_logs');
    }
};
