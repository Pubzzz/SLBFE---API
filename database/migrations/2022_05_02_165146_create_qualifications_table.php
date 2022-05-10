<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQualificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qualifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('citizen_id')->unsigned();
            $table->foreign('citizen_id')
                ->references('id')->on('citizens')->onDelete('cascade');
            $table->unsignedBigInteger('qualification_type_id')->unsigned();
            $table->foreign('qualification_type_id')
                ->references('id')->on('qualifications')->onDelete('cascade');
            $table->string('title', 60);
            $table->string('field', 80);
            $table->string('school_university', 80);
            $table->string('file_path')->nullable();
            $table->string('status', 10);
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
        Schema::dropIfExists('qualifications');
    }
}
