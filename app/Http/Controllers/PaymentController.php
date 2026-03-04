<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PaymentService;
use FedaPay\Event;
use FedaPay\FedaPay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function callback(Request $request)
    {
        FedaPay::setApiKey(config('services.fedapay.secret_key'));
        FedaPay::setEnvironment(config('services.fedapay.environment'));

        /** @var Event|null $event */
        $event = $request->attributes->get('fedapay_event');

        if (!$event) {
            Log::warning('FedaPay event manquant (middleware non exécuté ?)');
            return response()->json(['error' => 'Invalid webhook context'], 400);
        }

        if ($event->name !== 'transaction.approved') {
            return response()->json(['status' => 'ignored']);
        }

        $transactionId = $event->entity->id ?? null;
        if (!$transactionId) {
            return response()->json(['error' => 'Missing transaction id'], 400);
        }

        try {
            $transaction = \FedaPay\Transaction::retrieve($transactionId);
        } catch (\Throwable $e) {
            Log::error('Erreur récupération transaction FedaPay', [
                'transaction_id' => $transactionId,
                'message' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        $paiementId = $transaction->metadata->paiement_id ?? null;
        if (!$paiementId) {
            return response()->json(['error' => 'Missing metadata.paiement_id'], 400);
        }

        $paiement = Payment::find($paiementId);
        if (!$paiement) {
            return response()->json(['error' => 'Paiement not found'], 404);
        }

        if ($paiement->status === Payment::STATUS_PAID) {
            return response()->json(['status' => 'already_processed']);
        }

        try {
            $this->paymentService->confirmPaymentById($paiementId);
            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            Log::error('Erreur traitement webhook', [
                'paiement_id' => (string) $paiement->_id,
                'transaction_id' => (string) $transactionId,
                'message' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    public function redirectFromFedaPay(Request $request)
    {
        $status = strtolower((string) $request->query('status', ''));

        $frontBase = rtrim(config('app.front_url', config('app.url')), '/');

        if ($status === 'approved') {
            $path = '/success';
        } else {
            $path = '/failed';
        }

        $query = array_filter([
            'status' => $status ?: null,
            'transaction_id' => $request->query('transaction_id') ?? $request->query('id'),
        ]);

        $url = $frontBase . $path . (empty($query) ? '' : ('?' . http_build_query($query)));

        return redirect()->away($url);
    }
}