<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestion des secteurs</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white p-4 rounded shadow">
                <h3 class="font-semibold mb-3">Créer un secteur</h3>
                <form method="POST" action="{{ route('admin.sectors.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @csrf
                    <input class="border rounded px-3 py-2" name="name" placeholder="Nom" required />
                    <select class="border rounded px-3 py-2" name="domain_id" required>
                        <option value="">Sélectionner un domaine</option>
                        @foreach($domains as $domain)
                            <option value="{{ $domain->_id }}">{{ $domain->name }}</option>
                        @endforeach
                    </select>
                    <select class="border rounded px-3 py-2" name="level" required>
                        @foreach($levels as $level)
                            <option value="{{ $level }}">{{ ucfirst($level) }}</option>
                        @endforeach
                    </select>
                    <input class="border rounded px-3 py-2 md:col-span-2" name="description" placeholder="Description" />
                    <input class="border rounded px-3 py-2" name="total_slots" type="number" min="0" placeholder="Total places" required />
                    <div class="md:col-span-3">
                        <button class="bg-blue-600 text-white px-4 py-2 rounded">Créer</button>
                    </div>
                </form>
            </div>

            <div class="bg-white p-4 rounded shadow">
                <h3 class="font-semibold mb-3">Liste des secteurs</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-gray-500">
                            <tr>
                                <th class="py-2">Nom</th>
                                <th>Domaine</th>
                                <th>Statut</th>
                                <th>Niveau</th>
                                <th>Description</th>
                                <th>Totales</th>
                                <th>Disponibles</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sectors as $s)
                                <tr class="border-t align-top">
                                    <td class="py-3 font-medium">{{ $s->name }}</td>
                                    <td>{{ $s->domain?->name ?? '-' }}</td>
                                    <td>{{ ucfirst($s->status) }}</td>
                                    <td>{{ ucfirst($s->level) }}</td>
                                    <td>{{ $s->description }}</td>
                                    <td>{{ $s->total_slots }}</td>
                                    <td class="{{ $s->available_slots > 0 ? 'text-green-700' : 'text-red-700' }}">{{ $s->available_slots }}</td>
                                    <td class="text-right space-y-2">

                                        <form method="POST" action="{{ route('admin.sectors.resetSlots', $s->_id) }}">
                                            @csrf
                                            <button class="text-sm bg-gray-100 px-3 py-1 rounded hover:bg-gray-200">Reset slots</button>
                                        </form>

                                        <details class="text-left">
                                            <summary class="cursor-pointer text-blue-600 hover:underline">Modifier</summary>
                                            <form method="POST" action="{{ route('admin.sectors.update', $s->_id) }}" class="mt-2 grid grid-cols-1 gap-2">
                                                @csrf
                                                @method('PUT')
                                                <input class="border rounded px-3 py-2" name="name" value="{{ $s->name }}" required />
                                                <select class="border rounded px-3 py-2" name="domain_id" required>
                                                    @foreach($domains as $domain)
                                                        <option value="{{ $domain->_id }}" {{ (string) $s->domain_id === (string) $domain->_id ? 'selected' : '' }}>
                                                            {{ $domain->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <input class="border rounded px-3 py-2" value="{{ ucfirst($s->status) }}" disabled />
                                                <select class="border rounded px-3 py-2" name="level" required>
                                                    @foreach($levels as $level)
                                                        <option value="{{ $level }}" {{ $s->level === $level ? 'selected' : '' }}>{{ ucfirst($level) }}</option>
                                                    @endforeach
                                                </select>
                                                <input class="border rounded px-3 py-2" name="description" value="{{ $s->description }}" />
                                                <input class="border rounded px-3 py-2" name="total_slots" type="number" min="0" value="{{ $s->total_slots }}" required />
                                                <input class="border rounded px-3 py-2" name="available_slots" type="number" min="0" value="{{ $s->available_slots }}" required />
                                                <button class="bg-blue-600 text-white px-3 py-2 rounded">Enregistrer</button>
                                            </form>
                                        </details>

                                        <form method="POST" action="{{ route('admin.sectors.destroy', $s->_id) }}" onsubmit="return confirm('Supprimer ce secteur ?');">
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
