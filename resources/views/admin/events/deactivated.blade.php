@extends('layouts.app')

@section('title', 'Eventi disattivati - Admin')
@section('no_sidebar', '1')

@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <h1 class="display-4 mb-0">
                <i class="fas fa-eye-slash"></i> Eventi disattivati
            </h1>
            <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Torna a Gestione Eventi
            </a>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-calendar-plus"></i> Eventi disattivati futuri
                <span class="badge bg-secondary">{{ $upcoming->count() }}</span>
            </div>
            <div class="card-body">
                @include('admin.events.partials.deactivated-table', ['events' => $upcoming])
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="fas fa-history"></i> Eventi disattivati passati
                <span class="badge bg-secondary">{{ $past->count() }}</span>
            </div>
            <div class="card-body">
                @include('admin.events.partials.deactivated-table', ['events' => $past])
            </div>
        </div>
    </div>
@endsection
