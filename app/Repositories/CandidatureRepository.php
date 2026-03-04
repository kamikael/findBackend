<?php

namespace App\Repositories;

use App\Models\Candidature;

class CandidatureRepository
{
    public function create(array $data): Candidature
    {
        return Candidature::create($data);
    }

    public function find(string $id): ?Candidature
    {
        return Candidature::find($id);
    }

    public function attachPayment(string $candidatureId, string $paymentId): bool
    {
        $candidature = $this->find($candidatureId);

        if (!$candidature) {
            return false;
        }

        $candidature->payment_id = $paymentId;
        return (bool) $candidature->save();
    }

    public function countBySector(string $sectorId): int
    {
        return Candidature::where('sector_id', $sectorId)->count();
    }

    // ✅ utile pour stats/slots
    public function countPaidBySector(string $sectorId): int
    {
        return Candidature::where('sector_id', $sectorId)
            ->where('status', 'paid')
            ->count();
    }

    public function updateStatus(string $id, string $status): ?Candidature
    {
        $candidature = $this->find($id);

        if (!$candidature) {
            return null;
        }

        $candidature->status = $status;
        $candidature->save();

        return $candidature;
    }

    /**
     * ✅ Idempotence: passe à "paid" seulement si pas déjà "paid"
     * Retourne la candidature (mise à jour ou déjà payée), ou null si introuvable.
     */
    public function markPaidIfNotPaid(string $id): ?Candidature
    {
        $candidature = $this->find($id);
        if (!$candidature) {
            return null;
        }

        if ($candidature->status === 'paid') {
            return $candidature;
        }

        $candidature->status = 'paid';
        $candidature->save();

        return $candidature;
    }
}