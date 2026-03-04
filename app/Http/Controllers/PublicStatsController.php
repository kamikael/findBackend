<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Models\Sector;

class PublicStatsController extends Controller
{
    /**
     * GET /api/v1/public/stats
     * Stats globales publiques pour le frontend
     */
    public function index(): JsonResponse
    {
        // Nombre de secteurs "disponibles" (au moins 1 place restante)
        $availableSectorsCount = Sector::where('available_slots', '>', 0)->count();

        // Nombre total de places (tous secteurs confondus)
        $totalSlots = (int) Sector::sum('total_slots');

        // Nombre de places restantes (tous secteurs confondus)
        $remainingSlots = (int) Sector::sum('available_slots');

        return response()->json([
            'available_sectors_count' => $availableSectorsCount,
            'total_slots' => $totalSlots,
            'remaining_slots' => $remainingSlots,
        ]);
    }
}