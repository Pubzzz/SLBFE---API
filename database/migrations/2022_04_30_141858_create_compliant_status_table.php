<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompliantStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compliant_status', function (Blueprint $table) {
            $table->id();
            $table->string('status', 25);
            $table->text('comments');
            $table->unsignedBigInteger('compliant_id')->unsigned();
            $table->foreign('compliant_id')
                ->references('id')->on('compliants')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->unsigned();
            $table->foreign('user_id')
                ->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('compliant_status');
    }
}
