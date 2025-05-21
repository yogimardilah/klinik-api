<?php

namespace App\Services;

use App\Repositories\PasienRepositoryInterface;

class PasienService {
    protected $pasienRepository;

    public function __construct(PasienRepositoryInterface $pasienRepository) {
        $this->pasienRepository = $pasienRepository;
    }

    public function getAllPasien() {
        return $this->pasienRepository->all();
    }
    
      public function getPasiens(string $search = null, int $perPage = 10)
    {
        return $this->pasienRepository->getAll($search, $perPage);
    }

    public function getPasienById($id) {
        return $this->pasienRepository->find($id);
    }

    public function createPasien(array $data) {
        // Validasi atau logika lain bisa di sini
        return $this->pasienRepository->create($data);
    }

    public function updatePasien($id, array $data) {
        return $this->pasienRepository->update($id, $data);
    }

    public function deletePasien($id) {
        return $this->pasienRepository->delete($id);
    }
}
