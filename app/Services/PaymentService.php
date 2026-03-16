<?php

namespace App\Services;

use App\Repositories\CandidatureRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\SectorRepository;
use App\Models\Payment;
use App\Models\MobileMoneyProvider;
use FedaPay\Transaction;
use Illuminate\Support\Facades\Log;


class PaymentService
{
    public function __construct(
        protected PaymentRepository $paymentRepository,
        protected CandidatureRepository $candidatureRepository,
        protected SectorRepository $sectorRepository,
        protected EmailService $emailService,
    ) {
 \FedaPay\FedaPay::setApiKey(config('services.fedapay.secret_key'));
        \FedaPay\FedaPay::setEnvironment(config('services.fedapay.environment'));

    }

public function createPayment(string $candidatureId, int $amount, string $transactionId, string $providerId): Payment
{
    // 🔒 Sécurité : ne jamais créer un payment avec des ids vides
    if (empty($candidatureId)) {
        throw new \Exception('createPayment: candidatureId vide');
    }
    if (empty($providerId)) {
        throw new \Exception('createPayment: providerId vide');
    }

    $payment = $this->paymentRepository->create([
        'candidature_id' => $candidatureId, // ✅ clé exacte
        'provider_id'    => $providerId,    // ✅ clé exacte
        'amount'         => $amount,
        'transaction_id' => $transactionId,
        'status'         => Payment::STATUS_INITIATED,
    ]);

    // attacher au dossier candidature
    $this->candidatureRepository->attachPayment($candidatureId, (string) $payment->_id);

    return $payment;
}

   /**
 * ✅ Confirme un paiement à partir de l'ID Mongo du paiement.
 * C'est ce que ton webhook possède via metadata.paiement_id.
 */
public function confirmPaymentById(string $paymentId): ?Payment
{
    // 1️⃣ Trouver paiement
    $payment = $this->paymentRepository->findById($paymentId);

    if (!$payment) {
        return null;
    }

    // 🔒 Idempotence paiement
    if ($payment->status === Payment::STATUS_PAID) {
        return $payment;
    }

    // 2️⃣ Marquer paiement comme payé
    $payment = $this->paymentRepository->markAsPaid($paymentId);

    if (!$payment) {
        return null;
    }

    // 3️⃣ Charger candidature
    $candidature = $this->candidatureRepository->find(
        (string) $payment->candidature_id
    );

    if (!$candidature) {
        return $payment;
    }

    // 🔒 Idempotence candidature
    if ($candidature->status === 'paid') {
        return $payment;
    }

    // 4️⃣ Mettre candidature en paid
    $candidature = $this->candidatureRepository->updateStatus(
        (string) $candidature->_id,
        'paid'
    );

    if (!$candidature) {
        return $payment;
    }

    // 5️⃣ Décrément slots (atomique via ton SectorRepository)
    $requiredSlots = $candidature->level === 'Licence' ? 2 : 1;

    $decremented = $this->sectorRepository->decrementSlots(
        (string) $candidature->sector_id,
        $requiredSlots
    );

    if (!$decremented) {
        // ⚠️ Cas extrême : plus de places entre temps
        // Tu peux logguer mais ne remets PAS le paiement en arrière
        Log::warning('Paiement confirmé mais slots insuffisants', [
            'payment_id' => $paymentId,
            'sector_id' => $candidature->sector_id,
        ]);
    }

    // 6️⃣ Emails (protégés)
    try {
        $this->emailService->sendConfirmation($candidature->student_email);

        if (!empty($candidature->partner_email)) {
            $this->emailService->sendConfirmation($candidature->partner_email);
        }

    } catch (\Throwable $e) {
        Log::warning('Paiement confirmé mais email échoué', [
            'payment_id' => $paymentId,
            'message' => $e->getMessage(),
        ]);
    }

    return $payment;
}
    /**
     * Optionnel : si un jour tu veux confirmer via transaction_id FedaPay.
     */
    public function confirmPaymentByTransactionId(string $transactionId): ?Payment
    {
        $payment = $this->paymentRepository->findByTransactionId($transactionId);
        if (!$payment) {
            return null;
        }
        return $this->confirmPaymentById((string) $payment->_id);
    }

    public function createCheckout(
        Payment $paiement,
        string $customerEmail,
        string $phoneNumber,
        string $customerFirstname,
        string $customerLastname
    ): string
    {
        
        \Log::info('FedaPay config debug', [
  'env' => config('services.fedapay.environment'),
  'key_prefix' => substr((string) config('services.fedapay.secret_key'), 0, 12),
  'key_len' => strlen((string) config('services.fedapay.secret_key')),
]);
        try {
            $isSandbox = config('services.fedapay.environment') === 'sandbox';

            if ($isSandbox) {
                $phoneNumber = '64000001';
                $mode = 'momo_test';
                Log::info('FedaPay sandbox: utiliser 64000001 ou 66000001 pour succès.');
            } else {
                $provider = MobileMoneyProvider::find((string) $paiement->provider_id);

if (!$provider || empty($provider->code)) {
    throw new \Exception("Provider introuvable ou sans code pour payment #{$paiement->_id} (provider_id={$paiement->provider_id})");
}

$mode = $provider->code;
            }

            $baseBackUrl = config('app.back_url', config('app.url'));
            $callbackUrl = rtrim($baseBackUrl, '/') . '/fedapay/redirect';

            $transaction = Transaction::create([
                'description'  => "Paiement candidature #{$paiement->candidature_id}",
                'amount'       => 100,
                'currency'     => ['iso' => 'XOF'],
                'callback_url' => $callbackUrl,
                'mode'         => $mode,
                'customer'     => [
                    'firstname' => $customerFirstname,
                    'lastname' => $customerLastname,
                    'email' => $customerEmail,
                    'phone_number' => [
                        'country' => 'bj',
                        'number' => $phoneNumber??'22900000000',
                    ],
                ],
                'metadata' => [
                    // ✅ le plus sûr avec Mongo
                    'paiement_id' => (string) $paiement->_id,
                ],
            ]);

            $token = $transaction->generateToken();

            if (!$token || empty($token->url)) {
                throw new \Exception("Impossible de générer l'URL de paiement FedaPay.");
            }

            // (Optionnel) stocker transaction_id FedaPay sur le paiement
            try {
                $this->paymentRepository->setTransactionId((string) $paiement->_id, (string) $transaction->id);
            } catch (\Throwable $e) {
                Log::warning('Impossible de stocker transaction_id sur Payment', [
                    'payment_id' => (string) $paiement->_id,
                    'message' => $e->getMessage(),
                ]);
            }

            if ($isSandbox) {
                try {
                    $transaction->sendNowWithToken($mode, $token->token, [
                        'phone_number' => [
                            'number'  => $phoneNumber,
                            'country' => 'bj',
                        ],
                    ]);
                    Log::info('FedaPay sandbox: collect envoyé', ['paiement_id' => (string) $paiement->_id]);
                } catch (\Throwable $e) {
                    Log::warning('FedaPay sandbox sendNowWithToken', [
                        'paiement_id' => (string) $paiement->_id,
                        'message' => $e->getMessage(),
                    ]);
                }
            }

            return $token->url;
        } catch (\Throwable $e) {
           Log::error('Erreur création checkout FedaPay', [
        'class' => get_class($e),
        'message' => $e->getMessage(),
        'http_body' => method_exists($e, 'getHttpBody') ? $e->getHttpBody() : null,
        'http_status' => method_exists($e, 'getHttpStatus') ? $e->getHttpStatus() : null,
    ]);
    throw $e;
        }
    }
}
