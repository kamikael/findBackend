<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MobileMoneyProvider;
use Illuminate\Http\Request;

class AdminProviderController extends Controller
{
    public function index()
    {
        $providers = MobileMoneyProvider::orderBy('created_at', 'desc')->get();
        return view('admin.providers.index', compact('providers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:80',
            'code' => 'required|string|max:80',
            'country_iso' => 'required|string|size:2',
            'api_base_url' => 'nullable|string',
        ]);

        $data['is_active'] = true;

        MobileMoneyProvider::create($data);

        return redirect()->route('admin.providers.index')->with('success', 'Provider créé.');
    }

    public function update(string $id, Request $request)
    {
        $provider = MobileMoneyProvider::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:80',
            'code' => 'required|string|max:80',
            'country_iso' => 'required|string|size:2',
            'api_base_url' => 'nullable|string',
        ]);

        $provider->update($data);

        return redirect()->route('admin.providers.index')->with('success', 'Provider mis à jour.');
    }

    public function toggle(string $id)
    {
        $provider = MobileMoneyProvider::findOrFail($id);
        $provider->is_active = !$provider->is_active;
        $provider->save();

        return redirect()->route('admin.providers.index')->with('success', 'Provider mis à jour.');
    }

    public function destroy(string $id)
    {
        $provider = MobileMoneyProvider::findOrFail($id);
        $provider->delete();

        return redirect()->route('admin.providers.index')->with('success', 'Provider supprimé.');
    }
}