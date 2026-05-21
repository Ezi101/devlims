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
        Schema::table('utilizations', function (Blueprint $table) {
            $table->foreignId("chem_id")->nullable()->after('rpm');
            $table->string("chem_qty")->nullable()->after('chem_id');
            $table->foreignId("standard_id")->nullable()->after('chem_qty');
            $table->string("standard_qty")->nullable()->after('standard_id');
            $table->string("standard_batch")->nullable()->after('standard_qty');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('utilizations', function (Blueprint $table) {
            $table->dropColumn('chem_id');
            $table->dropColumn('chem_qty');
            $table->dropColumn('standard_id');
            $table->dropColumn('standard_qty');
            $table->dropColumn('standard_batch');
        });
    }
};
