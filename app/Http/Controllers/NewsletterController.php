<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc,dns', 'max:255'],
        ], [
            'email.required' => 'Informe um e-mail para se cadastrar na newsletter.',
            'email.email' => 'Informe um e-mail válido.',
        ]);

        $email = mb_strtolower(trim($validated['email']));

        $subscriber = NewsletterSubscriber::firstOrCreate(
            ['email' => $email],
            ['subscribed_at' => now()]
        );

        if (! $subscriber->wasRecentlyCreated) {
            return back()->with('success', 'Este e-mail já está cadastrado na newsletter.');
        }

        return back()->with('success', 'Cadastro realizado com sucesso na newsletter!');
    }
}
