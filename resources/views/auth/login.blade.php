@extends('layouts.app')

@section('title', 'Entrar - ' . config('app.name'))
@section('bodyClass', 'page-auth-login')

@section('content')
<section class="auth-page">
    <div class="container auth-login-shell">
        <div class="auth-card auth-card--center">
            @php($loginEmail = old('email', session('login_email')))
            <div class="auth-card__header">
                <span class="auth-card__icon"><i class="fa-regular fa-user"></i></span>
                <span class="eyebrow">Area do Cliente</span>
                <h1>{{ $loginEmail ? 'Digite o codigo' : 'Entrar ou cadastrar' }}</h1>
                <p>{{ $loginEmail ? 'Confira seu e-mail e informe o codigo de 6 numeros.' : 'Use apenas seu e-mail. Se ainda nao tiver conta, criamos uma para voce.' }}</p>
            </div>

            @if (session('info'))
                <div class="store-alert store-alert--success">{{ session('info') }}</div>
            @endif

            <form method="POST" action="{{ route('auth.login.submit') }}" class="form-stack">
                @csrf
                <div>
                    <label for="email">E-mail</label>
                    <input id="email" type="email" name="email" value="{{ $loginEmail }}" required autocomplete="email" placeholder="cliente.exemplo@email.com">
                    @error('email') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                @if($loginEmail)
                    <div>
                        <label for="code">Codigo recebido</label>
                        <input id="code" class="auth-code-input" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="6" name="code" value="{{ old('code') }}" required autocomplete="one-time-code" placeholder="000000">
                        @error('code') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                @endif

                <button type="submit" class="btn btn--full btn--green">{{ $loginEmail ? 'VALIDAR CODIGO' : 'RECEBER CODIGO' }}</button>
            </form>

            <div class="auth-benefits auth-benefits--compact">
                <span><i class="fa-solid fa-shield-halved"></i> Compra segura</span>
                <span><i class="fa-solid fa-box-open"></i> Rastreio facil</span>
                <span><i class="fa-solid fa-headset"></i> Atendimento rapido</span>
            </div>

            <p class="auth-switch">
                {{ $loginEmail ? 'Nao recebeu?' : 'Sem senha e sem cadastro demorado.' }}
                <a href="{{ route('auth.login') }}">{{ $loginEmail ? 'Enviar para outro e-mail' : 'Acesso por codigo no e-mail' }}</a>
            </p>
        </div>
    </div>
</section>
@endsection
