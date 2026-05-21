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
            $table->unsignedBigInteger('sub_test_id')->after('test')->nullable();
            $table->enum('is_forward',['yes','no'])->after('sub_test_id')->default('no');
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
            $table->dropColumn('sub_test_id');
            $table->dropColumn('is_forward');
        });
    }
};
