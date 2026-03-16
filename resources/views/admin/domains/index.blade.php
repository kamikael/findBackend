<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestion des domaines</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white p-4 rounded shadow">
                <h3 class="font-semibold mb-3">Créer un domaine</h3>
                <form method="POST" action="{{ route('admin.domains.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @csrf
                    <input class="border rounded px-3 py-2" name="name" placeholder="Nom du domaine" required />
                    <input class="border rounded px-3 py-2" name="description" placeholder="Description (optionnel)" />
                    <div class="md:col-span-2">
                        <button class="bg-blue-600 text-white px-4 py-2 rounded">Créer</button>
                    </div>
                </form>
            </div>

            <div class="bg-white p-4 rounded shadow">
                <h3 class="font-semibold mb-3">Liste des domaines</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-gray-500">
                            <tr>
                                <th class="py-2">Nom</th>
                                <th>Description</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($domains as $domain)
                                <tr class="border-t align-top">
                                    <td class="py-3 font-medium">{{ $domain->name }}</td>
                                    <td>{{ $domain->description }}</td>
                                    <td class="text-right space-y-2">
                                        <details class="text-left">
                                            <summary class="cursor-pointer text-blue-600 hover:underline">Modifier</summary>
                                            <form method="POST" action="{{ route('admin.domains.update', $domain->_id) }}" class="mt-2 grid grid-cols-1 gap-2">
                                                @csrf
                                                @method('PUT')
                                                <input class="border rounded px-3 py-2" name="name" value="{{ $domain->name }}" required />
                                                <input class="border rounded px-3 py-2" name="description" value="{{ $domain->description }}" />
                                                <button class="bg-blue-600 text-white px-3 py-2 rounded">Enregistrer</button>
                                            </form>
                                        </details>

                                        <form method="POST" action="{{ route('admin.domains.destroy', $domain->_id) }}" onsubmit="return confirm('Supprimer ce domaine ?');">
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
