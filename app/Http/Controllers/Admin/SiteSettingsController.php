<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SafeRichText;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteSettingsController extends Controller
{
    public function toggleFeature(Request $request, string $featureKey): RedirectResponse
    {
        $allowed = ['mercatino', 'chat_salottino', 'albums_foto', 'chat_reply_email_users'];
        if (! in_array($featureKey, $allowed, true)) {
            return redirect()->route('admin.dashboard')->with('error', 'Impostazione non valida.');
        }

        $new = SiteSettings::toggleBool('feature.' . $featureKey, true);

        $label = match ($featureKey) {
            'mercatino' => 'Mercatino',
            'chat_salottino' => 'Salottino chat',
            'albums_foto' => 'Album foto',
            'chat_reply_email_users' => 'Email risposta chat (utenti)',
            default => 'Funzione',
        };

        return redirect()
            ->route('admin.dashboard')
            ->with('success', $label . ': ' . ($new ? 'attivato' : 'nascosto') . '.');
    }

    /**
     * Promemoria "a evento concluso potrai votare" mostrato cliccando "Iscrivimi":
     * un unico controllo per attivarlo/disattivarlo e per impostarne la durata.
     */
    public function updateVoteReminderTooltip(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => 'sometimes|boolean',
            'duration_seconds' => 'required|integer|min:1|max:60',
        ]);

        $enabled = $request->boolean('enabled');

        SiteSettings::set('feature.vote_reminder_tooltip', $enabled);
        SiteSettings::set('site.vote_reminder_tooltip_duration', $validated['duration_seconds']);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Promemoria voto: ' . ($enabled ? 'attivato' : 'disattivato') . ', durata ' . $validated['duration_seconds'] . ' secondi.');
    }

    public function updateAnnouncement(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'message' => 'nullable|string|max:20000',
        ]);

        // Il testo arriva come HTML da CKEditor (colore testo, immagini, ecc.): va sanificato
        // allo stesso modo delle altre caselle di testo ricco del sito (forum, descrizione evento).
        $message = SafeRichText::sanitize((string) ($validated['message'] ?? ''), true);

        // CKEditor, quando lo si "svuota", lascia spesso un paragrafo vuoto (es. "<p><br></p>")
        // invece di una stringa vuota: senza questo controllo il messaggio resterebbe attivo
        // e gli utenti vedrebbero un box vuoto anche dopo averlo "cancellato".
        $hasVisibleContent = trim(preg_replace('/<[^>]+>/', ' ', $message)) !== '' || stripos($message, '<img') !== false;
        if (!$hasVisibleContent) {
            $message = '';
        }

        SiteSettings::set('site.announcement_message', $message);
        // Attivo automaticamente se c'è del testo, disattivo se il campo è vuoto:
        // niente interruttore separato da dimenticare di spuntare.
        SiteSettings::set('site.announcement_enabled', $message !== '');

        $successMessage = $message !== ''
            ? 'Messaggio salvato e attivo: verrà mostrato agli utenti al prossimo login.'
            : 'Messaggio rimosso: non verrà più mostrato.';

        return redirect()->route('home')->with('success', $successMessage);
    }
}
