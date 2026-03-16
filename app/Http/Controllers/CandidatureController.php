<?php

namespace App\Http\Controllers;

use App\Services\CandidatureService;
use App\Services\EmailService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CandidatureController extends Controller
{
    public function __construct(
        protected CandidatureService $candidatureService,
        protected PaymentService $paymentService,
        protected EmailService $emailService
    ) {}

    public function createCandidature(Request $request): JsonResponse
    {
        // 1) Validation des champs + fichiers
        $validated = $request->validate([
            'sector_id' => 'required|string',
            'level' => 'required|in:Licence,Master',
            'student_name' => 'required|string',
            'student_firstname' => 'required|string',
            'student_lastname' => 'required|string',
            'student_email' => 'required|email',
            'amount' => 'required|numeric|min:0',
            'provider_id' => 'required|string',

            // Binome (Licence)
            'partner_name' => 'nullable|string',
            'partner_firstname' => 'nullable|string',
            'partner_lastname' => 'nullable|string',
            'partner_email' => 'nullable|email',

            // Fichiers
            'student_cv' => 'required|file|mimes:pdf,doc,docx|max:2048', // 2MB
            'partner_cv' => 'nullable|file|mimes:pdf,doc,docx|max:2048',

            'phone_number' => 'required|string|min:8|max:20',
        ]);

        // 2) Regles niveau
        if ($validated['level'] === 'Licence') {
            if (
                empty($validated['partner_name']) ||
                empty($validated['partner_firstname']) ||
                empty($validated['partner_lastname']) ||
                empty($validated['partner_email'])
            ) {
                return response()->json(['message' => 'Binome requis pour Licence (partner_name, partner_firstname, partner_lastname, partner_email).'], 422);
            }

            if (!$request->hasFile('partner_cv')) {
                return response()->json(['message' => 'CV binome requis pour Licence (partner_cv).'], 422);
            }
        } else {
            // Master : pas de binome
            if (
                !empty($validated['partner_name']) ||
                !empty($validated['partner_firstname']) ||
                !empty($validated['partner_lastname']) ||
                !empty($validated['partner_email'])
            ) {
                return response()->json(['message' => 'Master doit etre individuel (pas de binome).'], 422);
            }
        }

        // 3) Upload CVs -> URLs
        $folder = 'cvs/' . now()->format('Y/m') . '/' . Str::uuid();

        $studentPath = $request->file('student_cv')->store($folder, 'public');
        $studentCvUrl = Storage::disk('public')->url($studentPath);

        $partnerCvUrl = null;
        if ($request->hasFile('partner_cv')) {
            $partnerPath = $request->file('partner_cv')->store($folder, 'public');
            $partnerCvUrl = Storage::disk('public')->url($partnerPath);
        }

        // 4) Preparer data candidature (avec urls)
        $candidatureData = [
            'sector_id' => $validated['sector_id'],
            'level' => $validated['level'],
            'student_name' => $validated['student_name'],
            'student_firstname' => $validated['student_firstname'],
            'student_lastname' => $validated['student_lastname'],
            'student_email' => $validated['student_email'],
            'student_cv_url' => $studentCvUrl,
            'partner_name' => $validated['level'] === 'Licence' ? $validated['partner_name'] : null,
            'partner_firstname' => $validated['level'] === 'Licence' ? $validated['partner_firstname'] : null,
            'partner_lastname' => $validated['level'] === 'Licence' ? $validated['partner_lastname'] : null,
            'partner_email' => $validated['level'] === 'Licence' ? $validated['partner_email'] : null,
            'partner_cv_url' => $validated['level'] === 'Licence' ? $partnerCvUrl : null,
            // status sera mis par service (pending / pending_payment)
        ];

        // 5) Creer candidature
        $candidature = $this->candidatureService->create($candidatureData);

        Log::info('DEBUG candidature id', [
            '_id' => $candidature->_id ?? null,
            '_id_str' => isset($candidature->_id) ? (string) $candidature->_id : null,
        ]);

        // 6) Creer payment (initiated) + checkout
        $payment = $this->paymentService->createPayment(
            (string) $candidature->_id,
            (int) $validated['amount'],
            (string) Str::uuid(),
            (string) $validated['provider_id']
        );

        Log::info('Payment cree', ['attrs' => $payment->getAttributes()]);

        $checkoutUrl = $this->paymentService->createCheckout(
            $payment,
            $candidature->student_email,
            $validated['phone_number'],
            $validated['student_firstname'],
            $validated['student_lastname']
        );

        // 7) Emails de soumission (non bloquants)
        
        try {
            $this->emailService->sendSubmissionConfirmation($candidature, (string) $candidature->student_email);

            if (!empty($candidature->partner_email)) {
                $this->emailService->sendSubmissionConfirmation($candidature, (string) $candidature->partner_email);
            }

        } catch (\Throwable $e) {
            Log::warning('Candidature enregistree mais email de soumission echoue', [
                'candidature_id' => (string) $candidature->_id,
                'message' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'candidature_id' => (string) $candidature->_id,
            'payment_id' => (string) $payment->_id,
            'payment_url' => $checkoutUrl,
            'student_cv_url' => $studentCvUrl,
            'partner_cv_url' => $partnerCvUrl,
        ], 201);
    }
}
