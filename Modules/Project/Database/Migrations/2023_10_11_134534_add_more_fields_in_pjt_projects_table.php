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
        Schema::table('pjt_projects', function (Blueprint $table) {
            $table->unsignedInteger('product_id')->nullable()->after('business_id');

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

        });
      
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pjt_projects', function (Blueprint $table) {
            $table->dropForeign('pjt_projects_product_id_foreign');
            $table->dropColumn('product_id');
        });
    }
};
