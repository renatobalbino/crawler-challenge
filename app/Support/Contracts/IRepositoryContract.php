<?php

namespace App\Support\Contracts;

interface IRepositoryContract
{
    public function all();
    public function findBy($field, $value);
    public function create(array $data);
    public function update(array $data, $id);
    public function delete($id);
    public function show($id);
}
