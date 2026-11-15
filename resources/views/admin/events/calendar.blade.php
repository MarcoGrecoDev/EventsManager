@extends('layouts.app')

@section('title', 'Calendario eventi - Admin')
@section('no_sidebar', '1')

@section('content')
    <style>
        @media print {
            .no-print { display: none !important; }
        }
    </style>

    <div class="container-fluid">
        <div class="no-print d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('admin.events.calendar', ['month' => $prevMonth]) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-chevron-left"></i> Mese precedente
                </a>
                <a href="{{ route('admin.events.calendar', ['month' => $nextMonth]) }}" class="btn btn-outline-secondary">
                    Mese successivo <i class="fas fa-chevron-right"></i>
                </a>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Stampa
                </button>
                <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Torna a Gestione Eventi
                </a>
            </div>
        </div>

        <h1 class="display-4 mb-4">
            <i class="fas fa-calendar-day"></i> Calendario — {{ $monthStart->locale('it')->translatedFormat('F Y') }}
        </h1>

        <div class="card">
            <div class="card-body p-0">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Giorno</th>
                            <th>Evento</th>
                            <th>Partecipanti</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($days as $day)
                            @php
                                $isToday = $day['date']->isToday();
                                $isWeekend = $day['date']->isWeekend();
                            @endphp
                            <tr class="{{ $isToday ? 'table-warning' : '' }}">
                                <td style="width: 1%; white-space: nowrap;" class="fw-semibold {{ $isWeekend ? 'text-danger' : '' }}">
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
                                        <div class="mb-1">
                                            {{ $event->participants_count }}
                                            @if($event->max_participants)
                                                / {{ (int) $event->max_participants }}
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
        </div>
    </div>
@endsection
