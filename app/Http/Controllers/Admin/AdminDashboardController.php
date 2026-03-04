<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sector;
use App\Models\Candidature;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // 1) Stats globales
        $sectorsCount = Sector::count();
        $totalSlots = (int) Sector::sum('total_slots');
        $availableSlots = (int) Sector::sum('available_slots');

        $candidaturesTotal = Candidature::count();
        $candidaturesPaid = Candidature::where('status', 'paid')->count();
        $candidaturesPending = Candidature::where('status', 'pending')->count();
        $candidaturesCancelled = Candidature::where('status', 'cancelled')->count();

        // 2) Liste secteurs
        $sectors = Sector::orderBy('available_slots', 'desc')->get();

        // 3) Map secteur_id => nom (pour afficher sector_name dans latestPaid)
        $sectorMap = $sectors->pluck('name', '_id')
            ->mapWithKeys(fn ($name, $id) => [(string) $id => $name]);

        // 4) Dernières candidatures payées
        $latestPaid = Candidature::where('status', 'paid')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($c) use ($sectorMap) {
                $c->sector_name = $sectorMap[(string) $c->sector_id] ?? (string) $c->sector_id;
                return $c;
            });

        // 5) Stats par secteur
        $sectorStats = $sectors->map(function ($s) {
            $paid = $s->candidatures()->where('status', 'paid')->count();
            $pending = $s->candidatures()->where('status', 'pending')->count();

            return [
                'name' => $s->name,
                'total_slots' => (int) $s->total_slots,
                'available_slots' => (int) $s->available_slots,
                'paid' => $paid,
                'pending' => $pending,
            ];
        });

        return view('admin.dashboard', compact(
            'sectorsCount',
            'totalSlots',
            'availableSlots',
            'candidaturesTotal',
            'candidaturesPaid',
            'candidaturesPending',
            'candidaturesCancelled',
            'sectors',
            'latestPaid',
            'sectorStats'
        ));
    }
}