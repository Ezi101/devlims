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
        Schema::table('s_t_r', function (Blueprint $table) {
            $table->string('verified_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->string('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('s_t_r', function (Blueprint $table) {
            $table->dropColumn('verified_by');
            $table->dropColumn('approved_by');
            $table->dropColumn('approved_at');
            $table->dropColumn('rejected_by');
            $table->dropColumn('rejected_at');

        });
    }
};
