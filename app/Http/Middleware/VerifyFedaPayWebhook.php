<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use FedaPay\Webhook as FedaPayWebhook;
use FedaPay\Error\SignatureVerification as FedaPaySignatureVerification;

class VerifyFedaPayWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        $signatureHeader = $request->header('X-FEDAPAY-SIGNATURE');
        $secret = config('services.fedapay.webhook_secret');
        $payload = $request->getContent();

        if (!$signatureHeader) {
            return response()->json(['error' => 'Missing signature'], 403);
        }

        if (!$secret) {
            Log::error('FedaPay webhook secret is not configured');

            return response()->json(['error' => 'Webhook not configured'], 500);
        }

        try {
            $event = FedaPayWebhook::constructEvent(
                $payload,
                $signatureHeader,
                $secret
            );
        } catch (\UnexpectedValueException $e) {
            Log::error('Webhook payload invalide: ' . $e->getMessage());

            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (FedaPaySignatureVerification $e) {
            Log::error('Signature webhook invalide: ' . $e->getMessage());

            return response()->json(['error' => 'Invalid signature'], 403);
        }

        // On attache l'event vérifié à la requête pour le controller
        $request->attributes->set('fedapay_event', $event);

        return $next($request);
    }
}
