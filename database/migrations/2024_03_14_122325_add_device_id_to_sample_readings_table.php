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
        Schema::table('sample_readings', function (Blueprint $table) {
            $table->foreignId('device_id')->nullable()->after('product_id');
            // If you're using Laravel version below 7.x, use the following instead:
            // $table->unsignedBigInteger('device_id')->nullable()->after('product_id');
            // $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sample_readings', function (Blueprint $table) {
            $table->dropForeign(['device_id']);
            $table->dropColumn('device_id');
        });
    }
};
