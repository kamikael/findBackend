<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Détails candidature</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white p-4 rounded shadow space-y-2">
                <div><strong>Statut :</strong> {{ $candidature->status }}</div>
                <div><strong>Niveau :</strong> {{ $candidature->level }}</div>
                <div><strong>Secteur :</strong> {{ $sector?->name ?? $candidature->sector_id }}</div>
                <hr class="my-2">

                <div><strong>Étudiant :</strong> {{ $candidature->student_name }}</div>
                <div><strong>Email :</strong> {{ $candidature->student_email }}</div>
                <div>
                    <strong>CV :</strong>
                    <a class="text-blue-600 hover:underline" href="{{ $candidature->student_cv_url }}" target="_blank">Ouvrir</a>
                </div>

                @if(!empty($candidature->partner_email))
                    <hr class="my-2">
                    <div><strong>Binôme :</strong> {{ $candidature->partner_name }}</div>
                    <div><strong>Email :</strong> {{ $candidature->partner_email }}</div>
                    <div>
                        <strong>CV binôme :</strong>
                        <a class="text-blue-600 hover:underline" href="{{ $candidature->partner_cv_url }}" target="_blank">Ouvrir</a>
                    </div>
                @endif
            </div>

            <div>
                <a class="text-blue-600 hover:underline" href="{{ route('admin.candidatures.index') }}">← Retour</a>
            </div>

        </div>
    </div>
</x-app-layout>