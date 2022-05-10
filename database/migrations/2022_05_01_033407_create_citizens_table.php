<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCitizensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('citizens', function (Blueprint $table) {
            $table->id();
            $table->string('nic', 15)->unique();
            $table->string('passport_no', 15)->nullable();
            $table->date('passport_expiry_date')->nullable();
            $table->string('profile_image_path')->default('images/profile.jpg');
            $table->date('date_of_birth');
            $table->string('mobile', 15);
            $table->string('profession', 60)->nullable();
            $table->string('employee_name', 80)->nullable();
            $table->unsignedBigInteger('user_id')->unsigned();
            $table->foreign('user_id')
                ->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('industry_id')->unsigned();
            $table->foreign('industry_id')
                ->references('id')->on('industries')->onDelete('cascade');
            $table->string('experience_level', 15);
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
        Schema::dropIfExists('citizens');
    }
}
