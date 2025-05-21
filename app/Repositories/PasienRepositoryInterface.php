<?php

namespace App\Repositories;

interface PasienRepositoryInterface {
    public function all();
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function getAll(string $search = null, int $perPage = 10);
}
