<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin Dashboard</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded shadow">
                    <div class="text-sm text-gray-500">Secteurs</div>
                    <div class="text-2xl font-bold">{{ $sectorsCount }}</div>
                </div>
                <div class="bg-white p-4 rounded shadow">
                    <div class="text-sm text-gray-500">Places totales</div>
                    <div class="text-2xl font-bold">{{ $totalSlots }}</div>
                </div>
                <div class="bg-white p-4 rounded shadow">
                    <div class="text-sm text-gray-500">Places disponibles</div>
                    <div class="text-2xl font-bold">{{ $availableSlots }}</div>
                </div>
                <div class="bg-white p-4 rounded shadow">
                    <div class="text-sm text-gray-500">Candidatures (total)</div>
                    <div class="text-2xl font-bold">{{ $candidaturesTotal }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded shadow">
                    <div class="text-sm text-gray-500">Payées</div>
                    <div class="text-2xl font-bold">{{ $candidaturesPaid }}</div>
                </div>
                <div class="bg-white p-4 rounded shadow">
                    <div class="text-sm text-gray-500">En attente</div>
                    <div class="text-2xl font-bold">{{ $candidaturesPending }}</div>
                </div>
                <div class="bg-white p-4 rounded shadow">
                    <div class="text-sm text-gray-500">Annulées</div>
                    <div class="text-2xl font-bold">{{ $candidaturesCancelled }}</div>
                </div>
            </div>

            <div class="bg-white p-4 rounded shadow">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold">Secteurs (places)</h3>
                    <a class="text-sm text-blue-600 hover:underline" href="{{ route('admin.sectors.index') }}">Gérer</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-gray-500">
                            <tr>
                                <th class="py-2">Nom</th>
                                <th>Totales</th>
                                <th>Disponibles</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sectors as $s)
                                <tr class="border-t">
                                    <td class="py-2">{{ $s->name }}</td>
                                    <td>{{ $s->total_slots }}</td>
                                    <td class="{{ $s->available_slots > 0 ? 'text-green-700' : 'text-red-700' }}">
                                        {{ $s->available_slots }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white p-4 rounded shadow">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold">Dernières candidatures payées</h3>
                    <a class="text-sm text-blue-600 hover:underline" href="{{ route('admin.candidatures.index', ['status' => 'paid']) }}">Voir tout</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-gray-500">
                            <tr>
                                <th class="py-2">Date</th>
                                <th>Étudiant</th>
                                <th>Email</th>
                                <th>Secteur</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($latestPaid as $c)
                                <tr class="border-t">
                                    <td class="py-2">{{ optional($c->updated_at)->format('Y-m-d H:i') }}</td>
                                    <td>{{ $c->student_name }}</td>
                                    <td>{{ $c->student_email }}</td>
                                    <td>{{ $c->sector_name }}</td>
                                    <td>
                                        <a class="text-blue-600 hover:underline" href="{{ route('admin.candidatures.show', $c->_id) }}">Détails</a>
                                    </td>
                                </tr>
                            @empty
                                <tr class="border-t">
                                    <td class="py-3 text-gray-500" colspan="5">Aucune candidature payée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="bg-white p-4 rounded shadow">
<h3 class="font-semibold mb-3">Statistiques par secteur</h3>
<table class="min-w-full text-sm">
<thead>
<tr>
<th>Secteur</th>
<th>Totales</th>
<th>Disponibles</th>
<th>Payées</th>
<th>En attente</th>
</tr>
</thead>
<tbody>
@foreach($sectorStats as $s)
<tr class="border-t">
<td>{{ $s['name'] }}</td>
<td>{{ $s['total_slots'] }}</td>
<td>{{ $s['available_slots'] }}</td>
<td class="text-green-700">{{ $s['paid'] }}</td>
<td class="text-yellow-700">{{ $s['pending'] }}</td>
</tr>
@endforeach
</tbody>
</table>
</div> 

        </div>
    </div>
</x-app-layout>