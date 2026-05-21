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
            $table->unsignedInteger('d_status_updated_by')->nullable()->after('demand_status');
            $table->unsignedInteger('d_status_approved_by')->nullable()->after('d_status_updated_by');
            $table->timestamp('demand_approved_at')->nullable()->after('d_status_approved_by');
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
            $table->dropColumn('d_status_updated_by');
            $table->dropColumn('d_status_approved_by');
            $table->dropColumn('demand_approved_at');
        });
    }
};
