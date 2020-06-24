<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehiclesAttributes extends Model
{

    protected $fillable = [
        'vehicle_id',
        'year',
        'model',
        'km',
        'gearbox',
        'doors',
        'fuel',
        'color',
        'plate',
        'exchange',
    ];

    public function vehicle()
    {
        return $this->belongsTo(\App\Models\Vehicle::class);
    }

}
