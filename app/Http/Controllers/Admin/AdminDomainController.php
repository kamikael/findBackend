<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use Illuminate\Http\Request;

class AdminDomainController extends Controller
{
    public function index()
    {
        $domains = Domain::orderBy('created_at', 'desc')->get();

        return view('admin.domains.index', compact('domains'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Domain::create($data);

        return redirect()->route('admin.domains.index')->with('success', 'Domaine créé.');
    }

    public function update(string $id, Request $request)
    {
        $domain = Domain::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $domain->update($data);

        return redirect()->route('admin.domains.index')->with('success', 'Domaine mis à jour.');
    }

    public function destroy(string $id)
    {
        $domain = Domain::findOrFail($id);

        if ($domain->sectors()->exists()) {
            return redirect()
                ->route('admin.domains.index')
                ->with('error', 'Impossible de supprimer ce domaine car il est déjà lié à des secteurs.');
        }

        $domain->delete();

        return redirect()->route('admin.domains.index')->with('success', 'Domaine supprimé.');
    }
}
