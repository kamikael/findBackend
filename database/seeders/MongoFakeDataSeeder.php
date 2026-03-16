<?php

namespace Database\Seeders;

use App\Models\Candidature;
use App\Models\Domain;
use App\Models\MobileMoneyProvider;
use App\Models\Payment;
use App\Models\Sector;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MongoFakeDataSeeder extends Seeder
{
    public function run(): void
    {
        Payment::query()->delete();
        Candidature::query()->delete();
        Sector::query()->delete();
        Domain::query()->delete();
        MobileMoneyProvider::query()->delete();

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

        $domains = collect([
            ['name' => 'Informatique', 'description' => 'Métiers du numérique'],
            ['name' => 'Comptabilité', 'description' => 'Finance, audit et gestion'],
            ['name' => 'Droit', 'description' => 'Affaires juridiques et conformité'],
        ])->mapWithKeys(function (array $domain) {
            $created = Domain::create($domain);

            return [$created->name => $created];
        });

        $sectors = collect([
            [
                'name' => 'Développement Web',
                'description' => 'Web / Mobile',
                'domain_name' => 'Informatique',
                'level' => 'license',
                'total_slots' => 20,
            ],
            [
                'name' => 'Réseaux & Sécurité',
                'description' => 'Sysadmin / Cyber',
                'domain_name' => 'Informatique',
                'level' => 'master',
                'total_slots' => 12,
            ],
            [
                'name' => 'Fiscalité & Audit',
                'description' => 'Audit / Comptabilité',
                'domain_name' => 'Comptabilité',
                'level' => 'license',
                'total_slots' => 15,
            ],
        ])->map(function (array $sector) use ($domains) {
            $domain = $domains->get($sector['domain_name']);

            return Sector::create([
                'name' => $sector['name'],
                'description' => $sector['description'],
                'domain_id' => (string) $domain->_id,
                'level' => $sector['level'],
                'total_slots' => (int) $sector['total_slots'],
                'available_slots' => (int) $sector['total_slots'],
            ]);
        });

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
                'provider_id' => (string) $provider->_id,
                'amount' => 5000,
                'status' => $status === 'paid' ? Payment::STATUS_PAID : Payment::STATUS_INITIATED,
                'transaction_id' => (string) Str::uuid(),
                'reference_transaction' => null,
            ]);

            $candidature->payment_id = (string) $payment->_id;
            $candidature->save();

            if ($status === 'paid') {
                $requiredSlots = $level === 'Licence' ? 2 : 1;
                $sector->available_slots = max(0, (int) $sector->available_slots - $requiredSlots);
                $sector->save();
            }
        }
    }
}
