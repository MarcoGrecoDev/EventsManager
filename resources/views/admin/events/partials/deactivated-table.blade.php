@if($events->isEmpty())
    <p class="text-muted mb-0">Nessun evento.</p>
@else
    <div class="table-responsive">
        <table class="table table-striped align-middle mb-0">
            <thead>
                <tr>
                    <th>Evento</th>
                    <th>Data</th>
                    <th>Città</th>
                    <th>Iscritti</th>
                    <th>Creato da</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                @foreach($events as $event)
                    <tr>
                        <td><strong>{{ $event->title }}</strong></td>
                        <td>{{ $event->date ? $event->date->format('d/m/Y H:i') : '—' }}</td>
                        <td>{{ $event->city }}</td>
                        <td>
                            <span class="badge bg-info">
                                {{ $event->participants_count }}
                                @if($event->max_participants)
                                    / {{ $event->max_participants }}
                                @endif
                            </span>
                        </td>
                        <td>
                            @if($event->user)
                                <a href="{{ route('profile.show', $event->user) }}" class="text-decoration-none">
                                    {{ $event->user->nickname }}
                                </a>
                            @else
                                <span class="text-muted">Utente cancellato</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-sm btn-outline-secondary" title="Modifica">
                                <i class="fas fa-edit"></i> Modifica
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
