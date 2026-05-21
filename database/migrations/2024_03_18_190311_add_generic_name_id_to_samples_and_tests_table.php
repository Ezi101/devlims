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
        Schema::table('samples_and_tests', function (Blueprint $table) {
            $table->unsignedBigInteger('generic_name_id')->nullable();

            // Add foreign key constraint
            $table->foreign('generic_name_id')
                ->references('id')
                ->on('generic_names')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('samples_and_tests', function (Blueprint $table) {
            $table->dropForeign(['generic_name_id']);

            // Drop the column
            $table->dropColumn('generic_name_id');
        });
    }
};
