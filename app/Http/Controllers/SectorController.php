<?php

namespace App\Http\Controllers;

use App\Services\SectorService;
use Illuminate\Http\JsonResponse;

class SectorController extends Controller
{
    public function __construct(
        protected SectorService $sectorService
    ) {}

    /**
     * GET /api/v1/sectors
     * Retourne uniquement les secteurs disponibles (available_slots > 0)
     */
    public function getSectors(): JsonResponse
    {
        try {
            $sectors = $this->sectorService->getAvailable();
            return response()->json($sectors, 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Error fetching sectors'], 500);
        }
    }

    /**
     * GET /api/v1/sectors/{id}
     * Retourne le détail d'un secteur
     */
    public function getSector(string $id): JsonResponse
    {
        try {
            $sector = $this->sectorService->getById($id);
            return response()->json($sector, 200);
        } catch (\Throwable $e) {
            // si le service throw "Sector not found."
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}