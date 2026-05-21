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
        Schema::table('s_o_p_s', function (Blueprint $table) {
            $table->string('category')->nullable()->after('title');
            $table->string('sub_category')->nullable()->after('title');
            $table->string('sop_starting_date')->nullable()->after('reference_code');
            $table->string('sop_expiry_date')->nullable()->after('reference_code');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('s_o_p_s', function (Blueprint $table) {
            $table->dropColumn('category');
            $table->dropColumn('sub_category');
            $table->dropColumn('sop_starting_date');
            $table->dropColumn('sop_expiry_date');
        });
    }
};
