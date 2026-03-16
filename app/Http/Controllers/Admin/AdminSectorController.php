<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminSectorController extends Controller
{
    private const ALLOWED_LEVELS = ['license', 'master'];

    public function index()
    {
        $sectors = Sector::with('domain')->orderBy('created_at', 'desc')->get();
        $domains = Domain::orderBy('name')->get();
        $levels = self::ALLOWED_LEVELS;

        return view('admin.sectors.index', compact('sectors', 'domains', 'levels'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'domain_id' => 'required|string',
            'level' => 'required|in:' . implode(',', self::ALLOWED_LEVELS),
            'total_slots' => 'required|integer|min:0',
        ]);

        $this->ensureDomainExists($data['domain_id']);
        $data['available_slots'] = (int) $data['total_slots'];

        Sector::create($data);

        return redirect()->route('admin.sectors.index')->with('success', 'Secteur créé.');
    }

    public function update(string $id, Request $request)
    {
        $sector = Sector::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'domain_id' => 'required|string',
            'level' => 'required|in:' . implode(',', self::ALLOWED_LEVELS),
            'total_slots' => 'required|integer|min:0',
            'available_slots' => 'required|integer|min:0',
        ]);

        $this->ensureDomainExists($data['domain_id']);

        if ((int) $data['available_slots'] > (int) $data['total_slots']) {
            $data['available_slots'] = (int) $data['total_slots'];
        }

        $sector->update($data);

        return redirect()->route('admin.sectors.index')->with('success', 'Secteur mis à jour.');
    }

    public function resetSlots(string $id)
    {
        $sector = Sector::findOrFail($id);
        $sector->available_slots = (int) $sector->total_slots;
        $sector->save();

        return redirect()->route('admin.sectors.index')->with('success', 'Places réinitialisées.');
    }

    public function destroy(string $id)
    {
        $sector = Sector::findOrFail($id);
        $sector->delete();

        return redirect()->route('admin.sectors.index')->with('success', 'Secteur supprimé.');
    }

    private function ensureDomainExists(string $domainId): void
    {
        if (!Domain::find($domainId)) {
            throw ValidationException::withMessages([
                'domain_id' => 'Le domaine sélectionné est invalide.',
            ]);
        }
    }
}
