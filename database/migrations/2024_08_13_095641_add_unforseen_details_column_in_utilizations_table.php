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
            $table->string('unforeseen_details')->nullable()->after('chem_qty');
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
            $table->dropColumn('unforeseen_details');
        });
    }
};
