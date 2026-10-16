{{-- Box "Ultimi Iscritti": stessa identica struttura di "Unisciti a noi" (solo
     intestazione cliccabile, nessuna lista visibile di default). Cliccandoci sopra si
     apre una finestra (modale) con gli ultimi 3 utenti registrati al sito. --}}
<div class="card card-sidebar mb-3" style="border: 1px solid #198754;">
    <div class="card-header py-2" role="button" data-bs-toggle="modal" data-bs-target="#recentSignupsModal" style="cursor:pointer;">
        <small class="fw-bold">
            Ultimi Iscritti
        </small>
    </div>
</div>

{{-- Box "Meteo": stessa identica struttura/dimensione di "Unisciti a noi" (solo
     intestazione con titolo breve). Al click apre le previsioni complete su un sito
     meteo esterno. --}}
<a href="https://www.ilmeteo.it/meteo/Milano" target="_blank" rel="noopener"
   class="card card-sidebar mb-3 text-decoration-none text-reset d-block"
   style="border: 1px solid #198754;"
   data-hint="Apri le previsioni meteo complete per Milano">
    <div class="card-header py-2">
        <small class="fw-bold">
            <i class="fas fa-cloud-sun text-success me-1"></i> Meteo
        </small>
    </div>
</a>

@push('modals')
<div class="modal fade" id="recentSignupsModal" tabindex="-1" aria-labelledby="recentSignupsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recentSignupsModalLabel">
                    <i class="fas fa-user-plus text-success me-1"></i> Ultimi Iscritti
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body">
                @php
                    try {
                        $recentSignups = \App\Models\User::query()
                            ->where('abilitato', 1)
                            ->orderByDesc('iscrittodal')
                            ->limit(3)
                            ->get(['userID', 'username']);
                    } catch (\Illuminate\Database\QueryException $e) {
                        $recentSignups = collect();
                    }
                @endphp
                @if($recentSignups->isEmpty())
                    <p class="text-muted mb-0">Nessun iscritto registrato.</p>
                @else
                    <ul class="list-unstyled mb-0">
                        @foreach($recentSignups as $recent)
                            <li class="py-1 border-bottom">
                                <i class="fas fa-user text-success me-1"></i>
                                <a href="{{ route('profile.show', $recent->userID) }}" class="text-decoration-none">
                                    {{ $recent->username }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Chiudi</button>
            </div>
        </div>
    </div>
</div>
@endpush
