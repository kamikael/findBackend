<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidature;
use App\Models\Sector;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class AdminCandidatureController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $sectorId = $request->query('sector_id');
        $q = $request->query('q');

        $query = Candidature::query()->orderBy('created_at', 'desc');

        if ($status) $query->where('status', $status);
        if ($sectorId) $query->where('sector_id', $sectorId);

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('student_name', 'like', "%{$q}%")
                    ->orWhere('student_email', 'like', "%{$q}%")
                    ->orWhere('partner_name', 'like', "%{$q}%")
                    ->orWhere('partner_email', 'like', "%{$q}%");
            });
        }

        $candidatures = $query->paginate(20);
        $sectors = Sector::orderBy('name')->get();

        $sectorMap = $sectors->pluck('name', '_id')
            ->mapWithKeys(fn($name, $id) => [(string)$id => $name]);

        $candidatures->getCollection()->transform(function ($c) use ($sectorMap) {
            $c->sector_name = $sectorMap[(string)$c->sector_id] ?? (string)$c->sector_id;
            return $c;
        });

        return view('admin.candidatures.index', compact('candidatures', 'sectors', 'status', 'sectorId', 'q'));
    }

    public function show(string $id)
    {
        $candidature = Candidature::findOrFail($id);
        $sector = Sector::find($candidature->sector_id);

        return view('admin.candidatures.show', compact('candidature', 'sector'));
    }

    public function cancel(string $id)
    {
        $c = Candidature::findOrFail($id);
        $c->status = 'cancelled';
        $c->save();

        return redirect()->back()->with('success', 'Candidature annulée.');
    }



public function exportPaid()
{
    $candidatures = Candidature::where('status', 'paid')
        ->orderBy('created_at', 'desc')
        ->get();

    $sectorMap = Sector::all()
        ->pluck('name', '_id')
        ->mapWithKeys(fn($name, $id) => [(string)$id => $name])
        ->toArray();

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Header
    $headers = [
        'Date', 'Secteur', 'Niveau', 'Statut',
        'Nom étudiant', 'Email étudiant', 'CV étudiant',
        'Nom binôme', 'Email binôme', 'CV binôme'
    ];
    $sheet->fromArray($headers, null, 'A1');

    // Rows
    $row = 2;
    foreach ($candidatures as $c) {
        $sheet->fromArray([
            optional($c->created_at)->format('Y-m-d H:i'),
            $sectorMap[(string)$c->sector_id] ?? (string)$c->sector_id,
            $c->level,
            $c->status,
            $c->student_name,
            $c->student_email,
            $c->student_cv_url,
            $c->partner_name,
            $c->partner_email,
            $c->partner_cv_url,
        ], null, "A{$row}");

        $row++;
    }

    // Download response
    $filename = 'candidatures_payees_' . now()->format('Ymd_His') . '.xlsx';
    $tempPath = storage_path("app/{$filename}");

    (new Xlsx($spreadsheet))->save($tempPath);

    return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
}
}