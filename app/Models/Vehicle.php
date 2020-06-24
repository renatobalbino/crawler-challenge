<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{

    protected $fillable = [
        'manufacturers_models_id',
        'name',
        'price',
        'slug',
    ];

    public function model()
    {
        return $this->belongsTo(\App\Models\ManufacturersModels::class, 'manufacturers_models_id', 'id');
    }

    public function attributes()
    {
        return $this->hasOne(\App\Models\VehiclesAttributes::class);
    }

    public function accessories()
    {
        return $this->hasOne(\App\Models\VehiclesAccessories::class);
    }
}
