<?php

namespace App\Repositories;

use App\Models\Pasien;

class PasienRepository implements PasienRepositoryInterface {
    protected $model;

    public function __construct(Pasien $pasien) {
        $this->model = $pasien;
    }

    public function all() {
        return $this->model->all();
    }

    public function find($id) {
        return $this->model->find($id);
    }

    public function create(array $data) {
        return $this->model->create($data);
    }

    public function update($id, array $data) {
        $pasien = $this->model->find($id);
        if($pasien) {
            $pasien->update($data);
            return $pasien;
        }
        return null;
    }

    public function delete($id) {
        return $this->model->destroy($id);
    }

     public function getAll(string $search = null, int $perPage = 10)
    {
        $query = Pasien::query();

        if ($search) {
            $query->where('nama', 'like', "%$search%");
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }
}
