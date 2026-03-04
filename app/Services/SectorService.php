<?php

namespace App\Services;

use App\Repositories\SectorRepository;
use Exception;

class SectorService
{
    public function __construct(
        protected SectorRepository $sectorRepository
        
    ) {}
    
    public function getAll()
    {
        return $this->sectorRepository->all();

    }

   public function getAvailable()
{
    return $this->sectorRepository->available();
}

    public function getById(string $id)
    {
        $sector = $this->sectorRepository->find($id);

        if (!$sector) {
            throw new Exception('Sector not found.');
        }

        return $sector;
    }

    public function create(array $data)
    {
        if (!isset($data['name']) || trim((string) $data['name']) === '') {
            throw new Exception('Name is required.');
        }

        if (!isset($data['total_slots']) || !is_numeric($data['total_slots'])) {
            throw new Exception('total_slots is required.');
        }

        $data['total_slots'] = (int) $data['total_slots'];
        if ($data['total_slots'] < 0) {
            throw new Exception('total_slots must be >= 0.');
        }

        return $this->sectorRepository->create($data);
    }

    public function update(string $id, array $data)
    {
        $sector = $this->sectorRepository->update($id, $data);

        if (!$sector) {
            throw new Exception('Sector not found.');
        }

        return $sector;
    }

    public function delete(string $id): void
    {
        $sector = $this->sectorRepository->find($id);

        if (!$sector) {
            throw new Exception('Sector not found.');
        }

        $sector->delete();
    }

    public function decrementSlots(string $sectorId, int $count = 1): void
    {
        $success = $this->sectorRepository->decrementSlots($sectorId, $count);

        if (!$success) {
            throw new Exception('No available slots in this sector.');
        }
    }

    public function resetSlots(string $sectorId): void
    {
        if (!$this->sectorRepository->resetSlots($sectorId)) {
            throw new Exception('Sector not found.');
        }
    }

    
}