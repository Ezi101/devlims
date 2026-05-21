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
            $table->string('sr_no')->nullable()->after('lab');
            $table->string('manual_id')->nullable()->after('sr_no');
            $table->string('sop')->nullable()->after('manual_id');
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
            $table->dropColumn(['sr_no', 'manual_id', 'sop']);
        });
    }
};
