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
            $table->unsignedBigInteger('qa_approved_by')->nullable()->after('verified_by');
            $table->timestamp('qa_approved_at')->nullable()->after('qa_approved_by');
            $table->unsignedBigInteger('oc_approved_by')->nullable()->after('qa_approved_at');
            $table->timestamp('oc_approved_at')->nullable()->after('oc_approved_by');
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
            $table->dropColumn('qa_approved_by');
            $table->dropColumn('qa_approved_at');
            $table->dropColumn('oc_approved_by');
            $table->dropColumn('oc_approved_at');
        });
    }
};
