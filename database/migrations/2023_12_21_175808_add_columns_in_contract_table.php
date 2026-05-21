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
            $table->string('entry_date',155)->nullable()->after('5th_installment');
            $table->string('expiry_date',155)->nullable()->after('entry_date');
            $table->string('packages_type',155)->nullable()->after('expiry_date');
            $table->string('number_of_packages',55)->nullable()->after('packages_type');
            $table->string('type',155)->nullable()->after('number_of_packages');
            $table->string('description',255)->nullable()->after('type');
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
            //
        });
    }
};
