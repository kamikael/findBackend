<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-xl">Providers Mobile Money</h2>
</x-slot>

<div class="py-8">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white p-4 rounded shadow">
    <h3 class="font-semibold mb-3">Créer un provider</h3>
    <form method="POST" action="{{ route('admin.providers.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
        @csrf
        <input class="border rounded px-3 py-2" name="name" placeholder="Nom (MTN/Moov)" required />
        <input class="border rounded px-3 py-2" name="code" placeholder="Code (mtn_momo)" required />
        <input class="border rounded px-3 py-2" name="country_iso" placeholder="bj" maxlength="2" required />
        <input class="border rounded px-3 py-2" name="api_base_url" placeholder="api_base_url (optionnel)" />
        <div class="md:col-span-4">
            <button class="bg-blue-600 text-white px-4 py-2 rounded">Créer</button>
        </div>
    </form>
</div>

<div class="bg-white p-4 rounded shadow">
    <h3 class="font-semibold mb-3">Liste</h3>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="text-left text-gray-500">
                <tr>
                    <th class="py-2">Nom</th>
                    <th>Code</th>
                    <th>Pays</th>
                    <th>Actif</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($providers as $p)
                    <tr class="border-t align-top">
                        <td class="py-3 font-medium">{{ $p->name }}</td>
                        <td>{{ $p->code }}</td>
                        <td>{{ strtoupper($p->country_iso) }}</td>
                        <td>
                            <span class="px-2 py-1 text-xs rounded {{ $p->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $p->is_active ? 'Oui' : 'Non' }}
                            </span>
                        </td>

                        <td class="text-right space-y-2">
                            <form method="POST" action="{{ route('admin.providers.toggle', $p->_id) }}">
                                @csrf
                                <button class="text-sm bg-gray-100 px-3 py-1 rounded hover:bg-gray-200">
                                    {{ $p->is_active ? 'Désactiver' : 'Activer' }}
                                </button>
                            </form>

                            <details class="text-left">
                                <summary class="cursor-pointer text-blue-600 hover:underline">Modifier</summary>
                                <form method="POST" action="{{ route('admin.providers.update', $p->_id) }}" class="mt-2 grid grid-cols-1 gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input class="border rounded px-3 py-2" name="name" value="{{ $p->name }}" required />
                                    <input class="border rounded px-3 py-2" name="code" value="{{ $p->code }}" required />
                                    <input class="border rounded px-3 py-2" name="country_iso" value="{{ $p->country_iso }}" maxlength="2" required />
                                    <input class="border rounded px-3 py-2" name="api_base_url" value="{{ $p->api_base_url }}" />
                                    <button class="bg-blue-600 text-white px-3 py-2 rounded">Enregistrer</button>
                                </form>
                            </details>

                            <form method="POST" action="{{ route('admin.providers.destroy', $p->_id) }}" onsubmit="return confirm('Supprimer ce provider ?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-sm bg-red-600 text-white px-3 py-1 rounded">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

</div>
</div>
</x-app-layout>