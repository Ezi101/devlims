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
            $table->foreignId('equipment_id')->nullable()->after('sub_test_id');
            $table->enum('test_type',['auto','manual'])->nullable()->after('equipment_id');
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
            $table->dropColumn('equipment_id');
            $table->dropColumn('test_type');
        });
    }
};
