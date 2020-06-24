<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManufacturersModels extends Model
{
    protected $fillable = [
        'manufacturer_id',
        'name',
        'description',
        'year',
        'model'
    ];

    public function manufacturer()
    {
        return $this->belongsTo(\App\Models\Manufacturer::class);
    }

    public function vehicles()
    {
        return $this->hasMany(\App\Models\Vehicle::class);
    }
}
