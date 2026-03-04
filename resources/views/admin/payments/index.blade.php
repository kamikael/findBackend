<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-xl">Paiements</h2>
</x-slot>

<div class="py-8">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

<div class="bg-white p-4 rounded shadow">
<div class="overflow-x-auto">
<table class="min-w-full text-sm">
<thead>
<tr class="text-left text-gray-500">
    <th class="py-2">Date</th>
    <th>Statut</th>
    <th>Montant</th>
    <th>Étudiant</th>
    <th>Email</th>
    <th>Secteur</th>
    <th>Transaction</th>
</tr>
</thead>
<tbody>
@foreach($payments as $p)
<tr class="border-t">
    <td class="py-2">{{ optional($p->created_at)->format('Y-m-d H:i') }}</td>
    <td>
        <span class="px-2 py-1 text-xs rounded
            {{ $p->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
            {{ $p->status === 'initiated' ? 'bg-yellow-100 text-yellow-800' : '' }}
            {{ $p->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
        ">
            {{ $p->status }}
        </span>
    </td>
    <td>{{ number_format($p->amount, 0, ',', ' ') }} FCFA</td>
    <td>{{ $p->student_name }}</td>
    <td>{{ $p->student_email }}</td>
    <td>{{ $p->sector_name }}</td>
    <td class="text-xs text-gray-500">{{ $p->transaction_id }}</td>
</tr>
@endforeach
</tbody>
</table>
</div>

<div class="mt-4">
{{ $payments->links() }}
</div>

</div>
</div>
</div>
</x-app-layout>