{{-- Pulsante + modale (solo admin) per impostare il messaggio popup di un singolo
     evento su "Accedi ai dettagli" (box benvenuto), senza aprire "Modifica evento".
     Se il messaggio non è attivo, cliccando "Accedi ai dettagli" si va dritti
     alla pagina dell'evento, senza nessun popup. --}}
@php
    $eventDbMissing = \App\Support\EventoTableSchema::missingOptionalColumns();
    $ggId = $event->getKey();
    $ggEnabled = (bool) ($event->greeting_box_enabled ?? false);
    $ggDuration = (int) ($event->greeting_box_duration ?? 2);
    $ggMessage = (string) ($event->greeting_box_message ?? '');
@endphp

@if($eventDbMissing === [])
    <button type="button"
            class="btn btn-outline-success btn-sm"
            data-bs-toggle="modal"
            data-bs-target="#eventGreetingModal{{ $ggId }}"
            data-hint="Messaggio popup (solo admin): compare quando un utente clicca «Accedi ai dettagli» su questo evento">
        <i class="fas fa-comment-dots"></i> Messaggio popup
    </button>

    @push('modals')
        <div class="modal fade" id="eventGreetingModal{{ $ggId }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <form id="eventGreetingForm{{ $ggId }}" method="POST"
                          action="{{ route('admin.events.greeting-box.update', $event) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-comment-dots text-success"></i>
                                Messaggio popup su «Accedi ai dettagli» (solo admin)
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="greeting_box_enabled" value="0">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="greeting_box_enabled_{{ $ggId }}" name="greeting_box_enabled" value="1"
                                       {{ $ggEnabled ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="greeting_box_enabled_{{ $ggId }}">
                                    Mostra un messaggio popup prima di aprire i dettagli di questo evento
                                </label>
                            </div>

                            <div id="eventGreetingFields{{ $ggId }}" style="{{ $ggEnabled ? '' : 'display:none;' }}">
                                <p class="text-muted small mb-3">
                                    Se disattivato (o se il messaggio resta vuoto), cliccando «Accedi ai dettagli»
                                    si apre direttamente la pagina dell'evento, senza nessun popup.
                                    Placeholder disponibili: <code>{nickname}</code>, <code>{nome}</code>,
                                    <code>{cognome}</code>, <code>{nome_completo}</code>.
                                </p>

                                <div class="mb-3" style="max-width: 220px;">
                                    <label for="greeting_box_duration_{{ $ggId }}" class="form-label fw-semibold">
                                        Durata popup (secondi)
                                    </label>
                                    <input type="number" class="form-control"
                                           id="greeting_box_duration_{{ $ggId }}"
                                           name="greeting_box_duration"
                                           min="1" max="120" value="{{ $ggDuration }}">
                                </div>

                                <label for="greeting_box_message_{{ $ggId }}" class="form-label fw-semibold">
                                    Messaggio popup
                                </label>
                                <textarea id="greeting_box_message_{{ $ggId }}"
                                          name="greeting_box_message"
                                          class="form-control"
                                          rows="6">{{ $ggMessage }}</textarea>

                                @include('partials.ckeditor4-description', ['field' => 'greeting_box_message_' . $ggId, 'height' => 180])
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annulla</button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Salva
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var toggle = document.getElementById(@json('greeting_box_enabled_' . $ggId));
                var fields = document.getElementById(@json('eventGreetingFields' . $ggId));
                if (toggle && fields) {
                    toggle.addEventListener('change', function () {
                        fields.style.display = toggle.checked ? '' : 'none';
                    });
                }

                var fieldId = @json('greeting_box_message_' . $ggId);
                var originalMessage = @json($ggMessage);
                var originalEnabled = @json($ggEnabled);
                var originalDuration = @json($ggDuration);

                var form = document.getElementById(@json('eventGreetingForm' . $ggId));
                if (form) {
                    form.addEventListener('submit', function () {
                        if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances[fieldId]) {
                            CKEDITOR.instances[fieldId].updateElement();
                        }
                    });
                }

                // Se la finestra viene chiusa senza salvare (Annulla, clic fuori, Esc),
                // scarta qualunque modifica non salvata: altrimenti CKEditor manterrebbe
                // in memoria il testo digitato e lo ripresenterebbe, non precompilato ma
                // "sporco", alla riapertura successiva nella stessa pagina.
                var modalEl = document.getElementById(@json('eventGreetingModal' . $ggId));
                if (modalEl) {
                    modalEl.addEventListener('hidden.bs.modal', function () {
                        if (toggle) {
                            toggle.checked = originalEnabled;
                        }
                        if (fields) {
                            fields.style.display = originalEnabled ? '' : 'none';
                        }
                        var durationInput = document.getElementById(@json('greeting_box_duration_' . $ggId));
                        if (durationInput) {
                            durationInput.value = originalDuration;
                        }
                        if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances[fieldId]) {
                            CKEDITOR.instances[fieldId].setData(originalMessage);
                        } else {
                            var textarea = document.getElementById(fieldId);
                            if (textarea) {
                                textarea.value = originalMessage;
                            }
                        }
                    });
                }
            });
        </script>
    @endpush
@endif
