<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programma eventi della settimana — {{ $weekStart->format('d/m/Y') }} - {{ $weekEnd->format('d/m/Y') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            color: #212529;
        }
        .print-toolbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            padding: 1rem;
            margin: -0.5rem -0.5rem 1.5rem;
        }
        .week-title {
            font-size: 1.4rem;
            margin-bottom: 0.25rem;
        }
        /* Niente box con bordo: le sezioni si susseguono come in un'email,
           separate solo da spazio e una sottile riga grigia. */
        .event-section {
            padding: 1rem 0;
            margin-bottom: 0.5rem;
            border-bottom: 1px solid #dee2e6;
            page-break-inside: avoid;
        }
        .event-section:last-of-type {
            border-bottom: none;
        }
        .event-section h2 {
            font-size: 1.2rem;
            margin-bottom: 0.4rem;
            color: #146c43;
            text-transform: uppercase;
        }
        .event-section .event-meta {
            font-size: 0.9rem;
            color: #495057;
            margin-bottom: 0.75rem;
            font-weight: 700;
        }
        .event-section .event-summary {
            font-size: 0.95rem;
            line-height: 1.55;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body class="p-3">
    <div class="no-print print-toolbar shadow-sm rounded mb-3">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            <strong class="me-2"><i class="fas fa-print"></i> Promemoria settimanale</strong>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Apri finestra di stampa
            </button>
            <a href="{{ route('admin.events.weekly-print', ['week' => $prevWeek]) }}" class="btn btn-outline-secondary">
                <i class="fas fa-chevron-left"></i> Settimana precedente
            </a>
            <a href="{{ route('admin.events.weekly-print', ['week' => $nextWeek]) }}" class="btn btn-outline-secondary">
                Settimana successiva <i class="fas fa-chevron-right"></i>
            </a>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Torna all'admin
            </a>
            <form action="{{ route('admin.events.weekly-print') }}" method="GET" class="d-flex align-items-center gap-2 ms-md-2">
                <label for="weekPicker" class="small text-muted mb-0">Vai a una settimana specifica:</label>
                <input type="date" id="weekPicker" name="week" class="form-control form-control-sm" style="width: auto;"
                       value="{{ $weekStart->format('Y-m-d') }}">
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="fas fa-calendar-check"></i> Vai
                </button>
            </form>
        </div>
        <p class="small text-muted mb-0">
            Puoi anche usare <kbd>Ctrl</kbd>+<kbd>P</kbd> (o <kbd>Cmd</kbd>+<kbd>P</kbd> su Mac) per stampare.
        </p>
    </div>

    <div class="container-fluid" style="max-width: 900px;">
        <header class="mb-4">
            <h1 class="week-title">
                <i class="fas fa-calendar-week"></i> Programma eventi della settimana
            </h1>
            <p class="text-muted mb-0">
                Dal <strong>{{ $weekStart->locale('it')->translatedFormat('l j F Y') }}</strong>
                al <strong>{{ $weekEnd->locale('it')->translatedFormat('l j F Y') }}</strong>
            </p>
        </header>

        @if($events->isEmpty())
            <p class="text-muted">Nessun evento pubblicato in questa settimana.</p>
        @else
            @foreach($events as $event)
                <div class="event-section">
                    <h2>
                        {{ $event->title }}
                        @if(!$event->is_active)
                            <span class="badge bg-secondary" style="text-transform: none;">non pubblicato</span>
                        @endif
                    </h2>
                    <div class="event-meta">
                        <i class="fas fa-clock"></i>
                        {{ $event->date ? $event->date->locale('it')->translatedFormat('l j F Y') . ' ore ' . $event->date->format('H:i') : '' }}
                        @if($event->city || $event->address)
                            &nbsp;·&nbsp;<i class="fas fa-map-marker-alt"></i>
                            {{ trim(implode(' — ', array_filter([$event->address, $event->city]))) }}
                        @endif
                        @if($event->formatted_cost)
                            &nbsp;·&nbsp;<i class="fas fa-euro-sign"></i> {{ $event->formatted_cost }}
                        @endif
                        @if($event->max_participants)
                            &nbsp;·&nbsp;<i class="fas fa-users"></i>
                            {{ $event->participants_count }}/{{ $event->max_participants }} posti
                        @endif
                        @if($event->deadline)
                            &nbsp;·&nbsp;<i class="fas fa-hourglass-half"></i>
                            iscrizioni entro {{ $event->deadline->locale('it')->translatedFormat('l j F') }}
                        @endif
                    </div>
                    <div class="event-summary">
                        @php
                            // Non si usa full_public_preview/incipit: l'incipit (breve
                            // riassunto) ha sempre la priorita' sulla descrizione completa
                            // in quell'accessor, quindi mostrerebbe sempre solo poche righe
                            // a prescindere da qualunque limite. Qui si usa invece sempre
                            // la descrizione completa dell'evento.
                            $eventMidSummary = trim(html_entity_decode(
                                strip_tags((string) $event->descrizione),
                                ENT_QUOTES | ENT_HTML5,
                                'UTF-8'
                            ));
                            $eventMidSummary = preg_replace('/\s+/u', ' ', $eventMidSummary) ?? $eventMidSummary;
                            // Riassunto a circa 1/3 della lunghezza originale (non un
                            // limite fisso): così resta proporzionato ai dati essenziali
                            // anche quando le descrizioni hanno lunghezze molto diverse.
                            if ($eventMidSummary !== '') {
                                $targetLen = max((int) ceil(mb_strlen($eventMidSummary, 'UTF-8') / 3), 150);
                                if (mb_strlen($eventMidSummary, 'UTF-8') > $targetLen) {
                                    $cut = mb_substr($eventMidSummary, 0, $targetLen, 'UTF-8');
                                    // Preferisci tagliare a fine frase (dopo un punto), non a
                                    // metà parola/frase: cerca l'ultimo ". " dentro il taglio.
                                    $lastSentenceEnd = mb_strrpos($cut, '. ', 0, 'UTF-8');
                                    if ($lastSentenceEnd !== false && $lastSentenceEnd >= $targetLen * 0.5) {
                                        $eventMidSummary = mb_substr($cut, 0, $lastSentenceEnd + 1, 'UTF-8');
                                    } else {
                                        // Nessun punto utile: taglia almeno a fine parola.
                                        $lastSpace = mb_strrpos($cut, ' ', 0, 'UTF-8');
                                        $eventMidSummary = ($lastSpace !== false ? mb_substr($cut, 0, $lastSpace, 'UTF-8') : $cut) . '…';
                                    }
                                }
                            }
                            // Molte descrizioni sono salvate tutte in maiuscolo: qui si
                            // trasformano in minuscolo, con la maiuscola solo dopo ogni
                            // punto (come una frase normale), solo per la visualizzazione.
                            if ($eventMidSummary !== '') {
                                $eventMidSummary = mb_strtolower($eventMidSummary, 'UTF-8');
                                $eventMidSummary = preg_replace_callback(
                                    '/(^\s*\w|[.!?]\s+\w)/u',
                                    fn ($m) => mb_strtoupper($m[0], 'UTF-8'),
                                    $eventMidSummary
                                );
                            }
                        @endphp
                        <p class="mb-0">{{ $eventMidSummary !== '' ? $eventMidSummary : 'Nessuna descrizione disponibile.' }}</p>
                    </div>
                </div>
            @endforeach

            <div class="mt-4 pt-3 border-top small text-center">
                <p class="mb-1 fw-bold">PER TUTTI I DETTAGLI, FOTO, PARTECIPANTI ECC.</p>
                <p class="mb-1">Sito: <a href="https://www.excursio.org" target="_blank" rel="noopener" class="fw-bold">www.excursio.org</a></p>
                <p class="mb-3">Mail: excursio@libero.it — Loris cell. 3387717376</p>
                <div class="text-muted text-start">
                    <p class="mb-1">
                        Questo messaggio ti viene inviato in conformità al Regolamento UE 2016/679 (GDPR)
                        e al D.Lgs. 196/2003 (Codice in materia di protezione dei dati personali).
                    </p>
                    <p class="mb-1">
                        Se non desideri ricevere ulteriori comunicazioni, puoi richiedere la cancellazione
                        immediata in qualsiasi momento:
                    </p>
                    <p class="mb-1">
                        Per non ricevere più email: invia un messaggio a excursio@libero.it inserendo come
                        oggetto "Cancellazione comunicazioni" e indicando nel testo l'indirizzo e-mail da rimuovere.
                    </p>
                    <p class="mb-0">
                        Per eliminare la registrazione dal sito: puoi richiedere la cancellazione completa del
                        tuo account e dei dati associati al sito Excursio scrivendo allo stesso indirizzo.
                    </p>
                </div>
            </div>
        @endif

    </div>
</body>
</html>
