<?php

namespace App\Repositories;

use App\Support\Repository;
use App\Models\VehiclesAttributes;

class VehiclesAttributesRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(app(VehiclesAttributes::class));
    }
}
