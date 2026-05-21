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
        Schema::table('deviations', function (Blueprint $table) {
            $table->foreignId('sample_id')->nullable()->after('type');
            $table->foreignId('batch_id')->nullable()->after('sample_id');
            $table->foreignId('test_id')->nullable()->after('batch_id');
            $table->string('lab')->nullable()->after('test_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('deviations', function (Blueprint $table) {
            $table->dropColumn('sample_id');
            $table->dropColumn('batch_id');
            $table->dropColumn('test_id');
            $table->dropColumn('lab');
        });
    }
};
