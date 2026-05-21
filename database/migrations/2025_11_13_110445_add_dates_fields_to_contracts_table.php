<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDatesFieldsToContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->date('eyenote_date')->nullable()->after('description');
            $table->date('acceptance_letter_date')->nullable()->after('eyenote_date');
            $table->date('iei_approved_date')->nullable()->after('acceptance_letter_date');
            $table->date('bulk_sampling_date')->nullable()->after('iei_approved_date');
            $table->date('desired_offered_date')->nullable()->after('bulk_sampling_date');
                        $table->enum('loc', ['lahore', 'karachi', 'rawalpindi'])->nullable()->after('desired_offered_date');

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
            $table->dropColumn([
                'eyenote_date',
                'acceptance_letter_date',
                'iei_approved_date',
                'bulk_sampling_date',
                'desired_offered_date','loc'
            ]);
        });
    }
}