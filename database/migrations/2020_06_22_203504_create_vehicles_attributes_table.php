<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiclesAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicles_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->foreign('vehicle_id')->references('id')->on('vehicles');
            $table->string('year', 4);
            $table->string('model', 4);
            $table->string('km');
            $table->string('gearbox')->default('manual');
            $table->string('doors', 3)->default(0);
            $table->string('fuel', 20)->default('gasolina');
            $table->string('color');
            $table->string('plate', 20);
            $table->integer('exchangeable')->default(0);
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
        Schema::dropIfExists('vehicles_attributes');
    }
}
