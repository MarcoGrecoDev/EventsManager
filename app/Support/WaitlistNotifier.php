<?php

namespace App\Support;

use App\Models\Event;
use App\Models\EventWaitlistEntry;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/**
 * Notifiche legate alla lista d'attesa: condivisa tra EventController
 * (iscrizione/cancellazione completa) e GuestController (rimozione di un
 * singolo ospite), visto che entrambe le azioni possono liberare un posto.
 */
class WaitlistNotifier
{
    public static function notifyAdminsEventSubscriptionChange(Event $event, User $actor, string $action): void
    {
        try {
            $adminId = (int) env('ADMIN_NOTIFY_ADMIN_ID', 0);
            $adminUsername = trim((string) env('ADMIN_NOTIFY_ADMIN_USERNAME', ''));

            $admin = User::query()
                ->where('ruolo', 0)
                ->when($adminId > 0, fn ($q) => $q->whereKey($adminId))
                ->when($adminId <= 0 && $adminUsername !== '', fn ($q) => $q->where('username', $adminUsername))
                ->when($adminId <= 0 && $adminUsername === '', fn ($q) => $q->where('username', 'scintilla'))
                ->first();

            $notifyEmail = trim((string) ($admin?->email ?? ''));
            if ($notifyEmail === '') {
                return;
            }

            $eventUrl = route('events.show', $event);
            $when = optional($event->date)->timezone(config('app.timezone'))->format('d/m/Y H:i');

            if ($action === 'iscritto' || $action === 'cancellato') {
                $adminGreeting = trim((string) ($admin->username ?? 'admin'));
                $userUsername = trim((string) ($actor->username ?? 'utente'));
                $userName = trim(trim((string) ($actor->nome ?? '')) . ' ' . trim((string) ($actor->cognome ?? '')));
                if ($userName === '') {
                    $userName = trim((string) ($actor->nickname ?? $userUsername));
                }

                $actionText = $action === 'iscritto'
                    ? 'si è iscritto ad evento'
                    : 'si è cancellato dall\'evento';

                $subject = 'Notifica iscrizioni evento';
                $body =
                    "Ciao {$adminGreeting}\n\n" .
                    "{$userUsername} - {$userName} {$actionText} {$event->title} {$when} {$eventUrl}\n";

                Mail::raw($body, function ($message) use ($notifyEmail, $subject) {
                    $message->to($notifyEmail)->subject($subject);
                });

                return;
            }

            $actorLabel = trim(($actor->nome ?? '') . ' ' . ($actor->cognome ?? ''));
            if ($actorLabel === '') {
                $actorLabel = $actor->nickname ?? ('ID ' . $actor->getKey());
            } else {
                $actorLabel .= ' (' . ($actor->nickname ?? $actor->getKey()) . ')';
            }

            $subject = "Excursio - Evento: utente {$action}";
            $body =
                "Notifica iscrizioni evento\n\n" .
                "Utente: {$actorLabel}\n" .
                "Azione: {$action}\n" .
                "Evento: {$event->title}\n" .
                "Quando: {$when}\n" .
                "Link evento: {$eventUrl}\n";

            Mail::raw($body, function ($message) use ($notifyEmail, $subject) {
                $message->to($notifyEmail)->subject($subject);
            });
        } catch (\Throwable $e) {
            // Non bloccare il flusso di iscrizione/cancellazione se la mail fallisce.
            \Log::warning('Notify admins subscription change failed: ' . $e->getMessage());
        }
    }

    /**
     * Se si e' liberato un posto (partecipante cancellato o un suo ospite
     * rimosso), avvisa il primo in lista d'attesa e, per conoscenza,
     * l'amministratore.
     */
    public static function notifyNextIfAny(Event $event): void
    {
        try {
            if (!$event->isRegistrationOpen()) {
                return;
            }
            if ($event->isFull()) {
                return;
            }

            $entry = EventWaitlistEntry::query()
                ->where('event_id', $event->getKey())
                ->where('status', 'pending')
                ->orderBy('created_at')
                ->first();

            if (!$entry) {
                return;
            }

            $user = User::query()->whereKey($entry->user_id)->first();
            $to = trim((string) ($user?->email ?? $entry->email ?? ''));
            if ($to === '') {
                return;
            }

            $eventUrl = route('events.show', $event);
            $when = optional($event->date)->timezone(config('app.timezone'))->format('d/m/Y H:i');
            $nome = trim((string) ($user?->nome ?? ''));
            $greeting = $nome !== '' ? "Ciao {$nome}!" : 'Ciao!';
            $subject = 'Excursio - Si è liberato un posto!';
            $body =
                "{$greeting}\n\n" .
                "Buone notizie: si è liberato un posto per questo evento:\n" .
                "{$event->title}\n" .
                "Quando: {$when}\n\n" .
                "Vai alla pagina evento e iscriviti appena puoi:\n" .
                "{$eventUrl}\n\n" .
                "A presto,\nExcursio\n";

            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });

            $entry->delete();

            if ($user) {
                self::notifyAdminsEventSubscriptionChange($event, $user, 'avvisato che si è liberato un posto in lista di attesa');
            }
        } catch (\Throwable $e) {
            \Log::warning('Notify waitlist failed: ' . $e->getMessage());
        }
    }
}
