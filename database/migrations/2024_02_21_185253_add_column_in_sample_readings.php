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
            $table->string('workflow_id')->nullable()->after('test_group_id');
            $table->string('task_id')->nullable()->after('workflow_id');
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
            //
        });
    }
};
