<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId('fiscal_year_id')
                  ->nullable()
                  ->constrained('fiscal_years')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign(['fiscal_year_id']);
            $table->dropColumn('fiscal_year_id');
        });
    }
};