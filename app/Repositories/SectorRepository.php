<?php

namespace App\Repositories;

use App\Models\Sector;

class SectorRepository
{
    public function all()
    {
        return Sector::orderBy('created_at', 'desc')->get();
    }

    /**
     * ✅ Secteurs disponibles (pour frontend)
     */
    public function available()
    {
        return Sector::where('available_slots', '>', 0)
            ->orderBy('available_slots', 'desc')
            ->get();
    }

    public function find(string $id): ?Sector
    {
        return Sector::find($id);
    }

    public function create(array $data): Sector
    {
        $data['total_slots'] = (int) $data['total_slots'];
        $data['available_slots'] = $data['available_slots'] ?? $data['total_slots'];

        // sécurité : available ne dépasse pas total
        if ((int) $data['available_slots'] > (int) $data['total_slots']) {
            $data['available_slots'] = (int) $data['total_slots'];
        }

        return Sector::create($data);
    }

    public function update(string $id, array $data): ?Sector
    {
        $sector = $this->find($id);

        if (!$sector) {
            return null;
        }

        // sécurité : available ne dépasse pas total si les deux sont présents
        if (isset($data['total_slots'], $data['available_slots'])) {
            if ((int) $data['available_slots'] > (int) $data['total_slots']) {
                $data['available_slots'] = (int) $data['total_slots'];
            }
        }

        $sector->update($data);

        return $sector;
    }

    /**
     * ✅ Décrément atomique (anti-concurrence)
     */
    public function decrementSlots(string $sectorId, int $count = 1): bool
    {
        if ($count <= 0) {
            return true;
        }

        $updated = Sector::where('_id', $sectorId)
            ->where('available_slots', '>=', $count)
            ->decrement('available_slots', $count);

        return $updated > 0;
    }

    public function resetSlots(string $sectorId): bool
    {
        $sector = $this->find($sectorId);

        if (!$sector) {
            return false;
        }

        $sector->available_slots = (int) $sector->total_slots;
        return (bool) $sector->save();
    }
}