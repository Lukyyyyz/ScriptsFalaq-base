@extends('layouts.app')

@section('title', 'Moderação — ' . $evento->titulo . ' — FalaQ')

@section('content')
<h4 class="fw-bold mb-3">🛡️ Fila de Moderação: {{ $evento->titulo }}</h4>

@if(session('sucesso'))
    <div class="alert alert-success">{{ session('sucesso') }}</div>
@endif

@forelse($moderacoes as $moderacao)
    <div class="card mb-3 shadow-sm">
        <div class="card-body">
            <p class="text-white mb-1">{{ $moderacao->pergunta->texto }}</p>
            <p class="text-secondary small mb-2">Denúncia: {{ $moderacao->motivo }}</p>
            <span class="badge bg-warning text-dark">{{ $moderacao->status }}</span>

            <form action="{{ route('moderacoes.status', $moderacao) }}" method="POST" class="mt-3 d-inline-block">
                @csrf
                @method('PATCH')
                <button name="status" value="aprovado" class="btn btn-sm btn-success fw-bold">Aprovar</button>
                <button name="status" value="rejeitado" class="btn btn-sm btn-danger fw-bold">Rejeitar</button>
            </form>
        </div>
    </div>
@empty
    <div class="alert alert-dark text-center p-4">Nenhuma denúncia pendente.</div>
@endforelse

<div class="d-flex justify-content-center">{{ $moderacoes->links() }}</div>
@endsection
