<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario eventi — {{ $monthStart->locale('it')->translatedFormat('F Y') }}</title>
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
        .calendar-title {
            font-size: 1.1rem;
            margin-bottom: 0.75rem;
            color: #000;
        }
        .calendar-title-month {
            font-size: 1.5rem;
            font-weight: 700;
            color: #8B4513;
            text-transform: uppercase;
        }
        table.calendar-table {
            font-size: 0.78rem;
        }
        table.calendar-table th,
        table.calendar-table td {
            padding: 0.2rem 0.4rem;
        }
        table.calendar-table thead th {
            font-size: 1.05rem;
        }
        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm;
            }
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
            .calendar-title { font-size: 1rem; margin-bottom: 0.4rem; }
            .calendar-title-month { font-size: 1.3rem; }
            table.calendar-table { font-size: 0.75rem; }
            table.calendar-table th, table.calendar-table td { padding: 0.12rem 0.35rem; }
            table.calendar-table thead th { font-size: 0.95rem; }
            table.calendar-table td div.mb-1 { margin-bottom: 0.1rem !important; }
        }
    </style>
</head>
<body class="p-3">
    <div class="no-print print-toolbar shadow-sm rounded mb-3">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <strong class="me-2"><i class="fas fa-calendar-day"></i> Calendario eventi</strong>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Stampa
            </button>
            <a href="{{ route('admin.events.calendar', ['month' => $prevMonth]) }}" class="btn btn-outline-secondary">
                <i class="fas fa-chevron-left"></i> Mese precedente
            </a>
            <a href="{{ route('admin.events.calendar', ['month' => $nextMonth]) }}" class="btn btn-outline-secondary">
                Mese successivo <i class="fas fa-chevron-right"></i>
            </a>
            <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Torna a Gestione Eventi
            </a>
        </div>
    </div>

    <div class="container-fluid">
        <h1 class="calendar-title">
            <i class="fas fa-calendar-day"></i> Calendario — <span class="calendar-title-month">{{ $monthStart->locale('it')->translatedFormat('F Y') }}</span>
        </h1>

        <table class="table table-striped align-middle mb-0 calendar-table">
            <thead>
                <tr>
                    <th>Giorno</th>
                    <th>Evento</th>
                    <th class="text-primary">Locale</th>
                    <th style="color: #8B4513;">Indirizzo</th>
                    <th>Partecipanti</th>
                </tr>
            </thead>
            <tbody>
                @foreach($days as $day)
                    @php
                        $isToday = $day['date']->isToday();
                        // Festività nazionali fisse (stesso giorno/mese ogni anno):
                        // Capodanno, Epifania, Liberazione, Festa del Lavoro, Festa
                        // della Repubblica, Ferragosto, 4 ottobre, Tutti i Santi,
                        // Immacolata, Natale, Santo Stefano.
                        $fixedHolidays = ['01-01', '01-06', '04-25', '05-01', '06-02', '08-15', '10-04', '11-01', '12-08', '12-25', '12-26'];
                        $isHoliday = in_array($day['date']->format('m-d'), $fixedHolidays, true);
                        // Lunedì dell'Angelo (Pasquetta): data mobile, calcolata ogni
                        // anno con la formula della Pasqua (funzione PHP easter_days).
                        $easterMonday = \Carbon\Carbon::create($day['date']->year, 3, 21)
                            ->addDays(easter_days($day['date']->year) + 1);
                        $isEasterMonday = $day['date']->isSameDay($easterMonday);
                        $isWeekend = $day['date']->isWeekend();
                        $isRedDay = $isWeekend || $isHoliday || $isEasterMonday;
                    @endphp
                    <tr class="{{ $isToday ? 'table-warning' : '' }}">
                        <td style="width: 1%; white-space: nowrap;" class="fw-semibold {{ $isRedDay ? 'text-danger' : '' }}">
                            {{ $day['date']->locale('it')->translatedFormat('l j') }}
                            @if($isToday)
                                <span class="badge bg-warning text-dark ms-1 no-print">Oggi</span>
                            @endif
                        </td>
                        <td>
                            @forelse($day['events'] as $event)
                                @php
                                    $isFutureInactive = !$event->is_active && $event->date && $event->date->isFuture();
                                    $eventColorClass = $event->is_active ? 'text-success' : ($isFutureInactive ? 'text-danger' : '');
                                @endphp
                                <div class="mb-1">
                                    <a href="{{ route('admin.events.edit', $event) }}" class="text-decoration-none fw-bold {{ $eventColorClass }}">
                                        {{ $event->title }}
                                    </a>
                                    @if(!$event->is_active)
                                        <span class="badge bg-secondary ms-1">Disattivato</span>
                                    @endif
                                </div>
                            @empty
                                <span class="text-muted">—</span>
                            @endforelse
                        </td>
                        <td>
                            @forelse($day['events'] as $event)
                                <div class="mb-1 text-primary">{{ $event->venue ?: '—' }}</div>
                            @empty
                                <span class="text-muted">—</span>
                            @endforelse
                        </td>
                        <td>
                            @forelse($day['events'] as $event)
                                <div class="mb-1" style="color: #8B4513;">{{ $event->address ?: '—' }}</div>
                            @empty
                                <span class="text-muted">—</span>
                            @endforelse
                        </td>
                        <td>
                            @forelse($day['events'] as $event)
                                <div class="mb-1">
                                    <span class="text-success fw-bold">{{ $event->participants_count }}</span>
                                    @if($event->max_participants)
                                        / <span class="text-danger fw-bold">{{ (int) $event->max_participants }}</span>
                                    @endif
                                </div>
                            @empty
                                <span class="text-muted">—</span>
                            @endforelse
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
