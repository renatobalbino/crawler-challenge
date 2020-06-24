<?php

namespace App\Repositories;

use App\Support\Repository;
use App\Models\Vehicle;

class VehiclesRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(app(Vehicle::class));
    }
}
