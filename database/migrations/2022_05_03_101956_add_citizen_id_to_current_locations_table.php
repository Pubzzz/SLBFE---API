<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCitizenIdToCurrentLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('current_locations', function (Blueprint $table) {
            $table->unsignedBigInteger('citizen_id')->unsigned();
            $table->foreign('citizen_id')
                ->references('id')->on('citizens')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('current_locations', function (Blueprint $table) {
            $table->dropColumn('citizen_id');
        });
    }
}
