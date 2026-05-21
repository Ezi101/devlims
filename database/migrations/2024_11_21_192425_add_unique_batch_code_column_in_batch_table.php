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
        Schema::table('batch', function (Blueprint $table) {
            $table->string('unique_batch_code')->after('code')->nullable();
            $table->string('transaction_id')->after('unique_batch_code')->nullable();
            $table->string('transaction_ref_no')->after('transaction_id')->nullable();
            $table->string('transaction_instalment')->after('transaction_ref_no')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('batch', function (Blueprint $table) {
            $table->dropColumn('unique_batch_code');
            $table->dropColumn('transaction_id');
            $table->dropColumn('transaction_ref_no');
            $table->dropColumn('transaction_instalment');
        });
    }
};
