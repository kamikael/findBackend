<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Candidature;
use App\Models\Sector;

class AdminPaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::orderBy('created_at', 'desc')->paginate(20);

        $candidatures = Candidature::whereIn('_id', $payments->pluck('candidature_id'))->get()
            ->keyBy(fn($c) => (string)$c->_id);

        $sectors = Sector::all()->pluck('name', '_id')
            ->mapWithKeys(fn($name, $id) => [(string)$id => $name]);

        $payments->getCollection()->transform(function ($p) use ($candidatures, $sectors) {
            $c = $candidatures[(string)$p->candidature_id] ?? null;
            $p->student_name = $c?->student_name;
            $p->student_email = $c?->student_email;
            $p->sector_name = $sectors[(string)($c?->sector_id)] ?? null;
            return $p;
        });

        return view('admin.payments.index', compact('payments'));
    }
}