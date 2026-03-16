<?php

namespace App\Services;

use App\Models\Candidature;
use App\Repositories\CandidatureRepository;
use App\Repositories\SectorRepository;
use Carbon\Carbon;
use Exception;
use App\Models\Payment;

class CandidatureService
{
    public function __construct(
        protected CandidatureRepository $candidatureRepository,
        protected SectorRepository $sectorRepository
    ) {}

    public function create(array $data)
    {
        $sectorId = (string) ($data['sector_id'] ?? '');
        if ($sectorId === '') {
            throw new Exception('sector_id is required.');
        }

        $sector = $this->sectorRepository->find($sectorId);
        if (!$sector) {
            throw new Exception('Sector not found.');
        }

        $data['level'] = isset($data['level']) ? trim((string) $data['level']) : null;

        $this->validateLevelRules($data);
        $this->ensureNoDuplicateSubmission(
            (string) ($data['student_email'] ?? ''),
            $sectorId
        );

        // 2 places pour Licence (binôme), 1 place pour Master (individuel)
        $requiredSlots = ($data['level'] === 'Licence') ? 2 : 1;

        if ((int) $sector->available_slots < $requiredSlots) {
            throw new Exception('Not enough available slots in this sector.');
        }

        // ✅ Aligné sur ton MLD : pending / paid / cancelled
        $data['status'] = 'pending';

        // On ne décrémente pas ici : ce sera fait après paiement confirmé (webhook)
        return $this->candidatureRepository->create($data);
    }

    private function validateLevelRules(array $data): void
    {
        $level = $data['level'] ?? null;
        if (!$level) {
            throw new Exception('Level is required.');
        }

        if ($level === 'Licence') {
            if (
                empty($data['partner_name']) ||
                empty($data['partner_firstname']) ||
                empty($data['partner_lastname']) ||
                empty($data['partner_email']) ||
                empty($data['partner_cv_url'])
            ) {
                throw new Exception('Missing required partner information for Licence level (binôme).');
            }
        } elseif ($level === 'Master') {
            // Candidature individuelle : pas de partenaire
            if (
                !empty($data['partner_name']) ||
                !empty($data['partner_firstname']) ||
                !empty($data['partner_lastname']) ||
                !empty($data['partner_email']) ||
                !empty($data['partner_cv_url'])
            ) {
                throw new Exception('Master level candidature must be individual (no partner).');
            }
        } else {
            throw new Exception('Invalid level value. Expected "Licence" or "Master".');
        }
    }

    private function ensureNoDuplicateSubmission(string $email, string $sectorId): void
{
    $email = trim($email);

    if ($email === '') {
        throw new Exception('Student email is required.');
    }

    $threeMonthsAgo = Carbon::now()->subMonths(3);

    $exists = Candidature::where('student_email', $email)
        ->whereHas('payment', function ($query) {
            $query->where('status', Payment::STATUS_PAID);
        })
        ->exists();

    if ($exists) {
        throw new Exception(
            'Vous avez déjà une candidature payée pour ce secteur au cours des 3 derniers mois.'
        );
    }
}
}
