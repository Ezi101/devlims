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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('afmsl_quantity')->nullable();
            $table->string('afims_quantity')->nullable();
            $table->string('user_quantity')->nullable();
            $table->string('afmsl_location')->nullable();
            $table->string('afims_location')->nullable();
            $table->string('user_location')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('afmsl_quantity');
            $table->dropColumn('afims_quantity');
            $table->dropColumn('user_quantity');
            $table->dropColumn('afmsl_location');
            $table->dropColumn('afims_location');
            $table->dropColumn('user_location');
        });
    }
};
