<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehiclesAccessories extends Model
{
    protected $fillable = [
        'vehicle_id',
        '1_air_bag',
        '2_air_bags',
        '3_air_bags',
        '4_air_bags',
        '5_air_bags',
        '6_air_bags',
        '7_air_bags',
        '8_air_bags',
        '9_air_bags',
        '10_air_bags',
        '11_air_bags',
        '12_air_bags',
        '7_lugares',
        'abs',
        'alarme',
        'ar_cond_digital',
        'ar_condicionado',
        'assist_partida_em_rampa',
        'awd',
        'banco_ajuste_altura',
        'bancos_de_couro',
        'blindado',
        'camera_de_re',
        'capota_maritima',
        'cd_mp3',
        'central_multimidia',
        'computador_de_bordo',
        'controle_estabilidade',
        'controle_tracao',
        'conversivel',
        'desembacador',
        'direcao_eletrica',
        'direcao_hidraulica',
        'dvd',
        'ebd',
        'engate',
        'farol_xenonio',
        'farois_de_milha',
        'gps',
        'limpador_traseiro',
        'mp3_usb',
        'piloto_automatico',
        'protetor_de_cacamba',
        'retrovisores_eletricos',
        'rebaixado',
        'rodas_de_liga_leve',
        'sensor_de_re',
        'teto-solar',
        'travas_eletricas',
        'tracao_4x4',
        'turbo',
        'vidros_eletricos',
        'volante_comando_multimidia',
        'volante_ajustavel',
    ];

    public function vehicle()
    {
        return $this->belongsTo(\App\Models\Vehicle::class);
    }
}
