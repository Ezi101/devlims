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
        Schema::table('contracts', function (Blueprint $table) {
            $table->decimal('contract_quantity', 22, 4)->default(0)->after('number');
            $table->decimal('received_quantity', 22, 4)->default(0)->after('contract_quantity');
            $table->string('status')->default('partial')->after('received_quantity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['contract_quantity', 'received_quantity', 'status']);
        });
    }
};
