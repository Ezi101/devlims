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
        Schema::table('contracts', function (Blueprint $table) {
            $table->year('fiscal_start_year')->nullable()->after('entry_date');
            $table->year('fiscal_end_year')->nullable()->after('fiscal_start_year');
            $table->date('fiscal_start_date')->nullable()->after('fiscal_end_year');
            $table->date('fiscal_end_date')->nullable()->after('fiscal_start_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['fiscal_start_year', 'fiscal_end_year', 'fiscal_start_date', 'fiscal_end_date']);
        });
    }
};
