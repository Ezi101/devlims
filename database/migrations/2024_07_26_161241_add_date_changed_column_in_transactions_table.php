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
            $table->datetime('d_fwd_to_afmsl')->nullable()->after('status');
            $table->datetime('d_fwd_to_2ic')->nullable()->after('d_fwd_to_afmsl');
            $table->datetime('d_rcv_by_afmsl')->nullable()->after('d_fwd_to_2ic');
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
            $table->dropColumn('d_fwd_to_afmsl');
            $table->dropColumn('d_fwd_to_2ic');
            $table->dropColumn('d_rcv_by_afmsl');
        });
    }
};
