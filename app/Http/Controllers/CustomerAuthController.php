<?php

namespace App\Http\Controllers;

use App\Mail\CustomerLoginCodeMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomerAuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('store.minha-conta');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        if ($request->filled('code')) {
            return $this->verifyLoginCode($request);
        }

        return $this->sendLoginCode($request);
    }

    public function showRegister(): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('store.minha-conta');
        }

        return redirect()->route('auth.login')
            ->with('info', 'Digite seu e-mail para entrar ou criar sua conta automaticamente.');
    }

    public function register(Request $request): RedirectResponse
    {
        return $this->login($request);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('store.index')
            ->with('success', 'Voce saiu da sua conta.');
    }

    public function account(): View|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('auth.login')
                ->with('info', 'Faca login para acessar sua conta.');
        }

        $user = Auth::user();
        $orders = Order::where('customer_email', $user->email)
            ->orderByDesc('created_at')
            ->get();

        return view('store.minha-conta', compact('user', 'orders'));
    }

    private function sendLoginCode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ], [
            'email.required' => 'Informe seu e-mail para receber o codigo.',
            'email.email' => 'Informe um e-mail valido.',
        ]);

        $email = Str::lower($validated['email']);
        $user = User::query()->where('email', $email)->first();

        if ($user?->is_admin) {
            return back()->withErrors(['email' => 'Use o painel de administrador para acessar.']);
        }

        $code = (string) random_int(100000, 999999);
        $expiresInMinutes = 10;

        DB::table('customer_login_codes')->insert([
            'email' => $email,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes($expiresInMinutes),
            'used_at' => null,
            'attempts' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Mail::to($email)->send(new CustomerLoginCodeMail($code, $expiresInMinutes));

        return redirect()->route('auth.login')
            ->with('login_email', $email)
            ->with('info', 'Enviamos um codigo de acesso para '.$email.'.');
    }

    private function verifyLoginCode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'digits:6'],
        ], [
            'code.required' => 'Digite o codigo que enviamos para seu e-mail.',
            'code.digits' => 'O codigo tem 6 numeros.',
        ]);

        $email = Str::lower($validated['email']);
        $code = $validated['code'];

        $loginCode = DB::table('customer_login_codes')
            ->where('email', $email)
            ->whereNull('used_at')
            ->where('expires_at', '>=', now())
            ->orderByDesc('id')
            ->first();

        if (! $loginCode || $loginCode->attempts >= 5 || ! Hash::check($code, $loginCode->code_hash)) {
            if ($loginCode) {
                DB::table('customer_login_codes')
                    ->where('id', $loginCode->id)
                    ->increment('attempts');
            }

            return back()
                ->with('login_email', $email)
                ->withErrors(['code' => 'Codigo invalido ou expirado. Peca um novo codigo.']);
        }

        DB::table('customer_login_codes')
            ->where('id', $loginCode->id)
            ->update([
                'used_at' => now(),
                'updated_at' => now(),
            ]);

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $this->nameFromEmail($email),
                'password' => Hash::make(Str::random(48)),
                'email_verified_at' => now(),
                'is_admin' => false,
            ],
        );

        if ($user->is_admin) {
            return back()->withErrors(['email' => 'Use o painel de administrador para acessar.']);
        }

        if (! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('store.minha-conta')
            ->with('success', 'Acesso liberado. Bem-vinda, '.$user->name.'!');
    }

    private function nameFromEmail(string $email): string
    {
        $name = Str::of(Str::before($email, '@'))
            ->replace(['.', '_', '-'], ' ')
            ->title()
            ->trim()
            ->toString();

        return $name !== '' ? $name : 'Cliente';
    }
}
