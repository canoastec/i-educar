@extends('layout.public')

@section('content')
    <h2 style="text-align: center;">Área destinada ao login de alunos para a realização da provas</h2>

    <div style="margin-top: 25px; text-align: center;">
        <a href="{{ route('google.redirect') }}">
            <img src="{{ asset('img/web_neutral_sq_na@2x.png') }}" alt="Entrar com Google" title="Entrar com Google">
        </a>
        <small style="display: block; text-align: center; color: #666; margin-top: 0.5rem; font-size: 0.8rem;">
            <i class="fa fa-info-circle"></i> Disponível apenas para alunos
        </small>
    </div>
@endsection
