<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('title', 60);
            $table->text('description');
            $table->unsignedBigInteger('industry_id')->unsigned();
            $table->foreign('industry_id')
                ->references('id')->on('industries')->onDelete('cascade');
            $table->unsignedBigInteger('company_id')->unsigned();
            $table->foreign('company_id')
                ->references('id')->on('companies')->onDelete('cascade');
            $table->date('application_deadline');
            $table->string('status', 15);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jobs');
    }
}
