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
        Schema::table('instruments', function (Blueprint $table) {
            $table->string('model')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('supplier')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('instruments', function (Blueprint $table) {
            $table->dropColumn('model');
            $table->dropColumn('manufacturer');
            $table->dropColumn('supplier');
        });
    }
};
