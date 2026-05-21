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
        Schema::table('s_o_p_s', function (Blueprint $table) {
            $table->string('method_no')->nullable();
            $table->unsignedInteger('sample_id')->nullable();
            $table->unsignedInteger('batch_no')->nullable();
            $table->unsignedInteger('generic_name_id')->nullable();
            $table->unsignedInteger('ptr_id')->nullable();
            $table->string('ptr_no')->nullable();
            $table->unsignedInteger('test_id')->nullable();
            $table->string('test_name')->nullable();
            $table->text('test_specifications')->nullable();
            $table->unsignedInteger('analyst_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('s_o_p_s', function (Blueprint $table) {
            $table->dropColumn([
                'method_no',
                'sample_id',
                'batch_no',
                'generic_name_id',
                'ptr_id',
                'ptr_no',
                'test_id',
                'test_name',
                'test_specifications',
                'analyst_id',
            ]);
        });
    }
};
