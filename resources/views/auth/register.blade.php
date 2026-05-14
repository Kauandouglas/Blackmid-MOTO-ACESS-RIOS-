@extends('layouts.app')

@section('title', 'Criar Conta - ' . config('app.name'))

@section('content')
<section class="auth-page">
    <div class="wrap auth-grid">
        <div class="auth-copy">
            <span class="eyebrow">Area do Cliente</span>
            <h1>Criar Conta</h1>
            <p>Crie sua conta para finalizar compras com mais velocidade e acompanhar seus pedidos.</p>
            <div class="auth-benefits">
                <span><i class="fa-solid fa-credit-card"></i> Mercado Pago</span>
                <span><i class="fa-solid fa-truck-fast"></i> Entrega acompanhada</span>
                <span><i class="fa-solid fa-motorcycle"></i> Produtos para sua moto</span>
            </div>
        </div>

        <div class="auth-card">
            <form method="POST" action="{{ route('auth.register.submit') }}" class="form-stack">
                @csrf

                <div>
                    <label for="name">Nome completo</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="Seu nome">
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email">E-mail</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="cliente.exemplo@email.com">
                    @error('email') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password">Senha</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Minimo 8 caracteres">
                    @error('password') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password_confirmation">Confirmar senha</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Repita a senha">
                </div>

                <button type="submit" class="btn btn--full btn--green">CRIAR CONTA</button>
            </form>

            <p class="auth-switch">
                Ja tem conta?
                <a href="{{ route('auth.login') }}">Entrar</a>
            </p>
        </div>
    </div>
</section>
@endsection
