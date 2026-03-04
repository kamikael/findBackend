<?php

namespace App\Services;

use App\Models\Candidature;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class EmailService
{
    public function sendConfirmation(string $email): void
    {
        Mail::raw(
            'Votre paiement a ete confirme. Merci pour votre candidature.',
            function (Message $message) use ($email): void {
                $message->to($email)
                    ->subject('Confirmation de paiement');
            }
        );
    }

    /**
     * Confirmation envoyee au candidat (ou binome) des la soumission.
     */
    public function sendSubmissionConfirmation(Candidature $candidature, string $email): void
    {
        $body = sprintf(
            "Votre candidature a bien ete enregistree.\n\nSecteur: %s\nNiveau: %s\nEtudiant: %s (%s)\nDate: %s\n\nVous recevrez un autre email une fois le paiement confirme.",
            $this->resolveSectorName($candidature),
            (string) $candidature->level,
            (string) $candidature->student_name,
            (string) $candidature->student_email,
            optional($candidature->created_at)->toDateTimeString()
        );

        Mail::raw(
            $body,
            function (Message $message) use ($email): void {
                $message->to($email)
                    ->subject('Confirmation de candidature');
            }
        );
    }

    /**
     * Notification envoyee a l'administrateur lors de la soumission.
     * Les CV sont joints quand ils existent sur le disque public.
     */
    public function sendAdminSubmissionNotification(Candidature $candidature): void
    {
        $adminEmail = config('mail.admin_address', config('mail.from.address'));

        if (!$adminEmail) {
            return;
        }

        $studentCvUrl = (string) $candidature->student_cv_url;
        $partnerCvUrl = (string) ($candidature->partner_cv_url ?? '');

        $body = sprintf(
            "Nouvelle candidature recue.\n\nSecteur: %s\nNiveau: %s\nEtudiant: %s (%s)\nBinome: %s (%s)\nDate: %s\n\nCV etudiant: %s\nCV binome: %s",
            $this->resolveSectorName($candidature),
            (string) $candidature->level,
            (string) $candidature->student_name,
            (string) $candidature->student_email,
            (string) ($candidature->partner_name ?? '-'),
            (string) ($candidature->partner_email ?? '-'),
            optional($candidature->created_at)->toDateTimeString(),
            $studentCvUrl !== '' ? $studentCvUrl : '-',
            $partnerCvUrl !== '' ? $partnerCvUrl : '-'
        );

        Mail::raw(
            $body,
            function (Message $message) use ($adminEmail, $candidature): void {
                $message->to($adminEmail)
                    ->subject('Nouvelle candidature');

                $this->attachCvIfExists($message, (string) $candidature->student_cv_url, 'cv-etudiant');
                $this->attachCvIfExists($message, (string) ($candidature->partner_cv_url ?? ''), 'cv-binome');
            }
        );
    }

    /**
     * Notification envoyee a l'administrateur quand le paiement est confirme.
     */
    public function sendAdminPaymentNotification(Candidature $candidature): void
    {
        $adminEmail = config('mail.admin_address', config('mail.from.address'));

        if (!$adminEmail) {
            return;
        }

        $body = sprintf(
            "Paiement confirme pour une candidature.\n\nSecteur: %s\nNiveau: %s\nEtudiant: %s (%s)\nDate: %s",
            $this->resolveSectorName($candidature),
            (string) $candidature->level,
            (string) $candidature->student_name,
            (string) $candidature->student_email,
            optional($candidature->created_at)->toDateTimeString()
        );

        Mail::raw(
            $body,
            function (Message $message) use ($adminEmail): void {
                $message->to($adminEmail)
                    ->subject('Paiement candidature confirme');
            }
        );
    }

    private function resolveSectorName(Candidature $candidature): string
    {
        return optional($candidature->sector)->name ?? (string) $candidature->sector_id;
    }

    private function attachCvIfExists(Message $message, ?string $publicUrl, string $defaultFilePrefix): void
    {
        if (empty($publicUrl)) {
            return;
        }

        $path = parse_url($publicUrl, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            return;
        }

        $relativePath = ltrim($path, '/');
        if (str_starts_with($relativePath, 'storage/')) {
            $relativePath = substr($relativePath, strlen('storage/'));
        }

        if (empty($relativePath) || !Storage::disk('public')->exists($relativePath)) {
            Log::warning('CV introuvable pour piece jointe email admin', [
                'url' => $publicUrl,
                'relative_path' => $relativePath,
            ]);
            return;
        }

        $absolutePath = Storage::disk('public')->path($relativePath);
        $extension = pathinfo($relativePath, PATHINFO_EXTENSION) ?: 'bin';
        $filename = $defaultFilePrefix . '.' . $extension;
        $mime = Storage::disk('public')->mimeType($relativePath) ?: 'application/octet-stream';

        $message->attach($absolutePath, [
            'as' => $filename,
            'mime' => $mime,
        ]);
    }
}
