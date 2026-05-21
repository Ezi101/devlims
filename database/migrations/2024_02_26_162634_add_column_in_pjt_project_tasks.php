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
        Schema::table('pjt_project_tasks', function (Blueprint $table) {
            $table->dateTime('completed_on')->nullable()->after('due_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pjt_project_tasks', function (Blueprint $table) {
            //
        });
    }
};
