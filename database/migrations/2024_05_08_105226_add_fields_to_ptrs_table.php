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
        Schema::table('p_t_r_s', function (Blueprint $table) {
            $table->unsignedInteger('remark_by')->nullable();
            $table->enum('remark_status', ['approved', 'rejected'])->nullable();
            $table->string('remark_date_time')->nullable();
            $table->string('rejected_at')->nullable()->after('approved_at');
            $table->unsignedInteger('remark_to')->nullable();
            $table->string('remark')->nullable();
            $table->string('approver_role')->nullable();
            $table->string('rejector_role')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('p_t_r_s', function (Blueprint $table) {
            $table->dropColumn('remark_by');
            $table->dropColumn('remark_status');
            $table->dropColumn('remark_date_time');
            $table->dropColumn('rejected_at');
            $table->dropColumn('remark_to');
            $table->dropColumn('remark');
            $table->dropColumn('approver_role');
            $table->dropColumn('rejector_role');
        });
    }
};
