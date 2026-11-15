@extends('layouts.app')

@section('title', 'Calendario eventi - Admin')
@section('no_sidebar', '1')

@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <h1 class="display-4 mb-0">
                <i class="fas fa-calendar-day"></i> Calendario — {{ $monthStart->locale('it')->translatedFormat('F Y') }}
            </h1>
            <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Torna a Gestione Eventi
            </a>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <table class="table table-striped align-middle mb-0">
                    <tbody>
                        @foreach($days as $day)
                            @php
                                $isToday = $day['date']->isToday();
                            @endphp
                            <tr class="{{ $isToday ? 'table-warning' : '' }}">
                                <td style="width: 1%; white-space: nowrap;" class="fw-semibold">
                                    {{ $day['date']->locale('it')->translatedFormat('l j') }}
                                    @if($isToday)
                                        <span class="badge bg-warning text-dark ms-1">Oggi</span>
                                    @endif
                                </td>
                                <td>
                                    @forelse($day['events'] as $event)
                                        <div class="mb-1">
                                            <a href="{{ route('admin.events.edit', $event) }}" class="text-decoration-none">
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
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
