<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manufacturer extends Model
{
    protected $fillable = [
        'name'
    ];

    public function models()
    {
        return $this->hasMany(\App\Models\ManufacturersModels::class);
    }
}
