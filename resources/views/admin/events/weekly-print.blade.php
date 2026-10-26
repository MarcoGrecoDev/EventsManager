<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promemoria settimanale eventi — {{ $weekStart->format('d/m/Y') }} - {{ $weekEnd->format('d/m/Y') }}</title>
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
        .event-card {
            border: 2px solid #198754;
            border-radius: 0.5rem;
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
            page-break-inside: avoid;
        }
        .event-card h2 {
            font-size: 1.15rem;
            margin-bottom: 0.4rem;
            color: #146c43;
        }
        .event-card .event-meta {
            font-size: 0.9rem;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        .event-card .event-summary {
            font-size: 0.95rem;
            line-height: 1.5;
            margin: 0;
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
        </div>
        <p class="small text-muted mb-0">
            Puoi anche usare <kbd>Ctrl</kbd>+<kbd>P</kbd> (o <kbd>Cmd</kbd>+<kbd>P</kbd> su Mac) per stampare.
        </p>
    </div>

    <div class="container-fluid" style="max-width: 900px;">
        <header class="mb-4">
            <h1 class="week-title">
                <i class="fas fa-calendar-week"></i> Eventi della settimana
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
                <div class="event-card">
                    <h2>{{ $event->title }}</h2>
                    <div class="event-meta">
                        <i class="fas fa-clock"></i>
                        {{ $event->date ? $event->date->locale('it')->translatedFormat('l j F Y') . ' ore ' . $event->date->format('H:i') : '' }}
                        @if($event->city || $event->address)
                            &nbsp;·&nbsp;<i class="fas fa-map-marker-alt"></i>
                            {{ trim(implode(' — ', array_filter([$event->address, $event->city]))) }}
                        @endif
                    </div>
                    <p class="event-summary">
                        {{ $event->short_preview !== '' ? $event->short_preview : 'Nessun riassunto disponibile.' }}
                    </p>
                </div>
            @endforeach
        @endif

        <p class="small text-muted mt-3 no-print">
            Generato il {{ now()->format('d/m/Y H:i') }} — Excursio
        </p>
    </div>
</body>
</html>
