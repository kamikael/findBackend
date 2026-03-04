<?php

namespace App\Repositories;

use App\Models\Payment;

class PaymentRepository
{
    public function create(array $data): Payment
    {
        return Payment::create($data);
    }

    public function findById(string $id): ?Payment
    {
        return Payment::find($id);
    }

    public function findByTransactionId(string $transactionId): ?Payment
    {
        return Payment::where('transaction_id', $transactionId)->first();
    }

    /**
     * ⚠️ Méthode existante: update status via transaction_id
     */
    public function updateStatus(string $transactionId, string $status): ?Payment
    {
        $payment = $this->findByTransactionId($transactionId);

        if (!$payment) {
            return null;
        }

        $payment->status = $status;
        $payment->save();

        return $payment;
    }

    /**
     * ✅ Recommandé: marquer comme payé via l'ID Mongo du paiement (webhook metadata.paiement_id)
     * Idempotent: si déjà payé, on ne refait rien.
     */
    public function markAsPaid(string $paymentId): ?Payment
    {
        $payment = $this->findById($paymentId);

        if (!$payment) {
            return null;
        }

        if ($payment->status === Payment::STATUS_PAID) {
            return $payment;
        }

        $payment->status = Payment::STATUS_PAID;
        $payment->paid_at = now(); // si tu as le champ
        $payment->save();

        return $payment;
    }

    /**
     * (Optionnel mais utile) stocker l'id FedaPay sur le paiement après createCheckout.
     */
    public function setTransactionId(string $paymentId, string $transactionId): ?Payment
    {
        $payment = $this->findById($paymentId);

        if (!$payment) {
            return null;
        }

        $payment->transaction_id = $transactionId;
        $payment->save();

        return $payment;
    }
}