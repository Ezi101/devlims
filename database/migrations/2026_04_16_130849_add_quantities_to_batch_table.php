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
            $table->decimal('afims_qty', 22, 4)->default(0)->after('quantity');
            $table->decimal('user_qty', 22, 4)->default(0)->after('afims_qty');
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
            $table->dropColumn(['afims_qty', 'user_qty']);
        });
    }
};
