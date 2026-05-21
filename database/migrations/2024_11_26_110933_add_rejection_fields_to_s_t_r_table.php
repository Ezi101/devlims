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
            $table->unsignedBigInteger('qa_rejected_by')->nullable()->after('verified_by');
            $table->timestamp('qa_rejected_at')->nullable()->after('qa_rejected_by');
            $table->unsignedBigInteger('oc_rejected_by')->nullable()->after('qa_rejected_at');
            $table->timestamp('oc_rejected_at')->nullable()->after('oc_rejected_by');
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
            $table->dropColumn('qa_rejected_by');
            $table->dropColumn('qa_rejected_at');
            $table->dropColumn('oc_rejected_by');
            $table->dropColumn('oc_rejected_at');
        });
    }
};
