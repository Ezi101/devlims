<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

class AddProjectPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Permission::create(['name' => 'workflow.create_project']);
        Permission::create(['name' => 'workflow.edit_project']);
        Permission::create(['name' => 'workflow.delete_project']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
