<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sector;
use Illuminate\Http\Request;

class AdminSectorController extends Controller
{
    public function index()
    {
        $sectors = Sector::orderBy('created_at', 'desc')->get();
        return view('admin.sectors.index', compact('sectors'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_slots' => 'required|integer|min:0',
        ]);

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
            'total_slots' => 'required|integer|min:0',
            'available_slots' => 'required|integer|min:0',
        ]);

        if ((int)$data['available_slots'] > (int)$data['total_slots']) {
            $data['available_slots'] = (int)$data['total_slots'];
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
}