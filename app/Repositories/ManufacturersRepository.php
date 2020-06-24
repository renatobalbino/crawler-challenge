<?php

namespace App\Repositories;

use App\Models\Manufacturer;
use App\Support\Repository;

class ManufacturersRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(app(Manufacturer::class));
    }
}
