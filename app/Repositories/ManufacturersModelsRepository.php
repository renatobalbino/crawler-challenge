<?php

namespace App\Repositories;

use App\Support\Repository;
use App\Models\ManufacturersModels;

class ManufacturersModelsRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(app(ManufacturersModels::class));
    }
}
