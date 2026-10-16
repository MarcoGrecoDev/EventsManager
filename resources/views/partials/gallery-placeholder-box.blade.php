{{-- Box "Ultimi Iscritti" (ex "Meteo weekend"): mostra gli ultimi 3 utenti registrati
     (iscrizione al sito, non login), stesso stile di "Ultimi accessi"/"Utenti online".
     Il link al meteo è stato spostato sotto il box, come semplice collegamento. --}}
<div class="card card-sidebar sidebar-box--green mb-3">
    <div class="card-header py-2">
        <small class="fw-bold">
            Ultimi Iscritti
        </small>
    </div>
    <div class="card-body p-2" style="height: 60px; min-height: 0; overflow-y: auto;">
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
            <small class="text-muted">Nessun iscritto registrato.</small>
        @else
            <ul class="list-unstyled mb-0">
                @foreach($recentSignups as $recent)
                    <li class="d-flex align-items-center online-user-row py-1">
                        <span class="online-dot"></span>
                        <span class="small text-truncate d-inline-block" style="max-width: 100%;">
                            <a href="{{ route('profile.show', $recent->userID) }}" class="text-decoration-none">
                                {{ $recent->username }}
                            </a>
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
<a href="https://www.ilmeteo.it/meteo/Milano" target="_blank" rel="noopener"
   class="d-block text-center small text-decoration-none mb-3"
   data-hint="Apri le previsioni meteo complete per Milano">
    <i class="fas fa-cloud-sun text-success me-1"></i> Meteo weekend
</a>
