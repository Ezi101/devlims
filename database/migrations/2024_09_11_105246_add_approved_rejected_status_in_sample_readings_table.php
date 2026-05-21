<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
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
        // Add 'approved' and 'rejected' to the existing 'status' enum
        DB::statement("ALTER TABLE sample_readings MODIFY COLUMN status ENUM('completed', 'not_started', 'in_progress', 'on_hold', 'cancelled', 'approved', 'rejected') DEFAULT 'not_started'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert to the original 'status' enum
        DB::statement("ALTER TABLE sample_readings MODIFY COLUMN status ENUM('completed', 'not_started', 'in_progress', 'on_hold', 'cancelled') DEFAULT 'not_started'");
    }
};
