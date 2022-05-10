<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('citizen_id')->unsigned();
            $table->foreign('citizen_id')
                ->references('id')->on('citizens')->onDelete('cascade');
            $table->string('address_line_one', 40);
            $table->string('address_line_two', 40)->nullable();
            $table->string('city', 40);
            $table->string('postal_code', 10)->nullable();
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
        Schema::dropIfExists('addresses');
    }
}
