<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Sector;
use App\Models\Candidature;
use App\Models\Payment;
use App\Models\MobileMoneyProvider;

class MongoFakeDataSeeder extends Seeder
{
    public function run(): void
    {
        // Nettoyage (optionnel, pratique en dev)
        MobileMoneyProvider::query()->delete();
        Sector::query()->delete();
        Candidature::query()->delete();
        Payment::query()->delete();

        // 1) Providers (Mongo)
        $mtn = MobileMoneyProvider::create([
            'name' => 'MTN',
            'code' => 'mtn_open',
            'country_iso' => 'bj',
            'is_active' => true,
            'api_base_url' => null,
        ]);

        $moov = MobileMoneyProvider::create([
            'name' => 'Moov',
            'code' => 'moov_open',
            'country_iso' => 'bj',
            'is_active' => true,
            'api_base_url' => null,
        ]);
        $celtis = MobileMoneyProvider::create([
            'name' => 'Celtis',
            'code' => 'celtis_open',
            'country_iso' => 'bj',
            'is_active' => true,
            'api_base_url' => null,
        ]);

        $providers = collect([$mtn, $moov, $celtis]);

        // 2) Sectors (Mongo)
        $sectors = collect([
            ['name' => 'Informatique', 'description' => 'Web / Mobile', 'total_slots' => 20],
            ['name' => 'Réseaux & Sécurité', 'description' => 'Sysadmin / Cyber', 'total_slots' => 12],
            ['name' => 'Finance', 'description' => 'Audit / Comptabilité', 'total_slots' => 15],
        ])->map(function ($s) {
            return Sector::create([
                'name' => $s['name'],
                'description' => $s['description'],
                'total_slots' => (int) $s['total_slots'],
                'available_slots' => (int) $s['total_slots'],
            ]);
        });

        // 3) Candidatures + Payments (Mongo) (liés)
        for ($i = 1; $i <= 10; $i++) {
            $sector = $sectors->random();
            $provider = $providers->random();

            $level = ($i % 2 === 0) ? 'Licence' : 'Master';
            $isLicence = $level === 'Licence';

            $status = ($i % 3 === 0) ? 'paid' : 'pending';

            $candidature = Candidature::create([
                'sector_id' => (string) $sector->_id,
                'level' => $level,
                'student_name' => "Etudiant $i",
                'student_email' => "etudiant{$i}@demo.com",
                'student_cv_url' => "https://example.com/cv/etudiant{$i}.pdf",
                'partner_name' => $isLicence ? "Binome $i" : null,
                'partner_email' => $isLicence ? "binome{$i}@demo.com" : null,
                'partner_cv_url' => $isLicence ? "https://example.com/cv/binome{$i}.pdf" : null,
                'status' => $status,
                'payment_id' => null,
            ]);

            $payment = Payment::create([
                'candidature_id' => (string) $candidature->_id,
                'provider_id'    => (string) $provider->_id,   // ✅ ICI
                'amount'         => 5000,
                'status'         => $status === 'paid' ? Payment::STATUS_PAID : Payment::STATUS_INITIATED,
                'transaction_id' => (string) Str::uuid(),
                'reference_transaction' => null,
            ]);

            $candidature->payment_id = (string) $payment->_id;
            $candidature->save();

            // décrémenter places si "paid"
            if ($status === 'paid') {
                $requiredSlots = $level === 'Licence' ? 2 : 1;
                $sector->available_slots = max(0, (int) $sector->available_slots - $requiredSlots);
                $sector->save();
            }
        }
    }
}