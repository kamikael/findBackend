<?php

namespace App\Services;

use App\Models\Candidature;
use App\Models\Payment;

class StatsService
{
    public function totalCandidatures(): int
    {
        return Candidature::count();
    }

    public function totalRevenue(): int
    {
        return Payment::where('status', 'paid')->sum('amount');
    }

    public function paidCandidatures(): int
    {
        return Candidature::where('status', 'paid')->count();
    }
}
