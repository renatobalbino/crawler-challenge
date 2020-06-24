<?php

namespace App\Repositories;

use App\Support\Repository;
use App\Models\VehiclesAccessories;

class VehiclesAccessoriesRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(app(VehiclesAccessories::class));
    }
}
