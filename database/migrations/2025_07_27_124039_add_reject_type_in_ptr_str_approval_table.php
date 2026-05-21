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
        Schema::table('ptr_str_approval', function (Blueprint $table) {
            $table->string('reject_type')->default('observation')->nullable()->after('remark');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ptr_str_approval', function (Blueprint $table) {
            $table->dropColumn('reject_type');
        });
    }
};
