<?php

namespace App\Repositories;

use App\Support\Repository;
use App\Models\Page;

class PagesRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(app(Page::class));
    }
}
