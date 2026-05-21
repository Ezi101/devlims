<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 
     * 
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pjt_project_tasks', function (Blueprint $table) {
            $table->string('test')->nullable()->after('task_id');
            $table->string('test_status')->nullable()->after('test');
            $table->string('test_on_issue_id')->nullable()->after('test_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
