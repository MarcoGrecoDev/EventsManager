@extends('layouts.app')

@section('no_sidebar', '1')
@section('title', 'Recupero username - Excursio')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-id-card"></i> Recupero username</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <p class="text-muted small">
                    Inserisci l'email che hai usato in fase di registrazione: ti invieremo
                    il tuo username a quello stesso indirizzo.
                </p>

                <form method="POST" action="{{ route('username.send') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Invia username per email
                        </button>
                    </div>
                </form>

                <hr>
                <div class="text-center">
                    <a href="{{ route('password.request') }}">Hai perso anche la password?</a>
                </div>
                <div class="text-center mt-1">
                    <a href="{{ route('login') }}">Torna al login</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
