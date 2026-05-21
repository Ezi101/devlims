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
            $table->string('category')->nullable()->after('description');
            $table->string('sub_category')->nullable()->after('category');
            $table->date('instrument_expiry')->nullable()->after('model');
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
            $table->dropColumn('category');
            $table->dropColumn('sub_category');
            $table->dropColumn('instrument_expiry');
        });
    }
};
