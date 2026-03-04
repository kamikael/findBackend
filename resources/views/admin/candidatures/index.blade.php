<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Candidatures</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white p-4 rounded shadow">
                <form method="GET" action="{{ route('admin.candidatures.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <input class="border rounded px-3 py-2" name="q" value="{{ request('q') }}" placeholder="Recherche nom/email..." />   
                <select class="border rounded px-3 py-2" name="status">
                        <option value="">Tous statuts</option>
                        <option value="paid" {{ $status==='paid' ? 'selected' : '' }}>Payé</option>
                        <option value="pending" {{ $status==='pending' ? 'selected' : '' }}>En attente</option>
                        <option value="cancelled" {{ $status==='cancelled' ? 'selected' : '' }}>Annulé</option>
                    </select>

                    <select class="border rounded px-3 py-2" name="sector_id">
                        <option value="">Tous secteurs</option>
                        @foreach($sectors as $s)
                            <option value="{{ $s->_id }}" {{ (string)$sectorId === (string)$s->_id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                    

                    <button class="bg-blue-600 text-white px-4 py-2 rounded">Filtrer</button>
                    <a href="{{ route('admin.candidatures.exportPaid') }}"
   class="bg-green-600 text-white px-4 py-2 rounded">
   Export CSV (Payées)
</a>
                </form>
            </div>

            <div class="bg-white p-4 rounded shadow">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-gray-500">
                            <tr>
                                <th class="py-2">Date</th>
                                <th>Statut</th>
                                <th>Étudiant</th>
                                <th>Email</th>
                                <th>Niveau</th>
                                <th>Secteur</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($candidatures as $c)
                                <tr class="border-t">
                                    <td class="py-2">{{ optional($c->created_at)->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <span class="px-2 py-1 rounded text-xs
                                            {{ $c->status==='paid' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $c->status==='pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $c->status==='cancelled' ? 'bg-gray-100 text-gray-800' : '' }}
                                        ">
                                            {{ $c->status }}
                                        </span>
                                    </td>
                                    <td>{{ $c->student_name }}</td>
                                    <td>{{ $c->student_email }}</td>
                                    <td>{{ $c->level }}</td>
                                    <td>{{ $c->sector_id }}</td>
                                    <td class="text-right">
                                        <a class="text-blue-600 hover:underline" href="{{ route('admin.candidatures.show', $c->_id) }}">Détails</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $candidatures->withQueryString()->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>