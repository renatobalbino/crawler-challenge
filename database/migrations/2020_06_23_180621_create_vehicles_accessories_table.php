<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiclesAccessoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicles_accessories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->foreign('vehicle_id')->references('id')->on('vehicles');
            $table->integer('1_air_bag')->length(1)->default(0);
            $table->integer('2_air_bags')->length(1)->default(0);
            $table->integer('3_air_bags')->length(1)->default(0);
            $table->integer('4_air_bags')->length(1)->default(0);
            $table->integer('5_air_bags')->length(1)->default(0);
            $table->integer('6_air_bags')->length(1)->default(0);
            $table->integer('7_air_bags')->length(1)->default(0);
            $table->integer('8_air_bags')->length(1)->default(0);
            $table->integer('9_air_bags')->length(1)->default(0);
            $table->integer('10_air_bags')->length(1)->default(0);
            $table->integer('11_air_bags')->length(1)->default(0);
            $table->integer('12_air_bags')->length(1)->default(0);
            $table->integer('7_lugares')->length(1)->default(0);
            $table->integer('abs')->length(1)->default(0);
            $table->integer('alarme')->length(1)->default(0);
            $table->integer('ar_cond_digital')->length(1)->default(0);
            $table->integer('ar_condicionado')->length(1)->default(0);
            $table->integer('assist_partida_em_rampa')->length(1)->default(0);
            $table->integer('awd')->length(1)->default(0);
            $table->integer('banco_ajuste_altura')->length(1)->default(0);
            $table->integer('bancos_de_couro')->length(1)->default(0);
            $table->integer('blindado')->length(1)->default(0);
            $table->integer('camera_de_re')->length(1)->default(0);
            $table->integer('capota_marítima')->length(1)->default(0);
            $table->integer('cd_mp3')->length(1)->default(0);
            $table->integer('central_multimidia')->length(1)->default(0);
            $table->integer('computador_de_bordo')->length(1)->default(0);
            $table->integer('controle_estabilidade')->length(1)->default(0);
            $table->integer('controle_tracao')->length(1)->default(0);
            $table->integer('conversivel')->length(1)->default(0);
            $table->integer('desembacador')->length(1)->default(0);
            $table->integer('direcao_eletrica')->length(1)->default(0);
            $table->integer('direcao_hidraulica')->length(1)->default(0);
            $table->integer('dvd')->length(1)->default(0);
            $table->integer('ebd')->length(1)->default(0);
            $table->integer('engate')->length(1)->default(0);
            $table->integer('farol_xenonio')->length(1)->default(0);
            $table->integer('farois_de_milha')->length(1)->default(0);
            $table->integer('gps')->length(1)->default(0);
            $table->integer('limpador_traseiro')->length(1)->default(0);
            $table->integer('mp3_usb')->length(1)->default(0);
            $table->integer('piloto_automatico')->length(1)->default(0);
            $table->integer('protetor_de_cacamba')->length(1)->default(0);
            $table->integer('retrovisores_eletricos')->length(1)->default(0);
            $table->integer('rebaixado')->length(1)->default(0);
            $table->integer('rodas_de_liga_leve')->length(1)->default(0);
            $table->integer('sensor_de_re')->length(1)->default(0);
            $table->integer('teto-solar')->length(1)->default(0);
            $table->integer('travas_eletricas')->length(1)->default(0);
            $table->integer('traçao_4x4')->length(1)->default(0);
            $table->integer('turbo')->length(1)->default(0);
            $table->integer('vidros_eletricos')->length(1)->default(0);
            $table->integer('volante_comando_multimidia')->length(1)->default(0);
            $table->integer('volante_ajustavel')->length(1)->default(0);
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
        Schema::dropIfExists('vehicles_accessories');
    }
}
