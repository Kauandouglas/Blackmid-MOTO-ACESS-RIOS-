<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSettingController extends Controller
{
    private const STORE_KEYS = [
        'app.name',
        'app.contact_email',
        'store.shipping_fee',
        'store.shipping_provider',
        'store.origin.name',
        'store.origin.street1',
        'store.origin.street2',
        'store.origin.city',
        'store.origin.postcode',
        'store.origin.country',
        'store.origin.phone',
        'store.origin.email',
        'store.melhorenvio.token',
        'store.melhorenvio.base_url',
    ];

    private const PIXEL_KEYS = [
        'store.pixel.facebook',
        'store.pixel.facebook_currency',
    ];

    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => $this->settingsFor(self::STORE_KEYS),
        ]);
    }

    public function editPixels(): View
    {
        return view('admin.settings.pixels', [
            'settings' => $this->settingsFor(self::PIXEL_KEYS),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'app.name'                          => ['required', 'string', 'max:120'],
            'app.contact_email'                 => ['nullable', 'email', 'max:120'],
            'store.shipping_fee'                => ['required', 'numeric', 'min:0'],
            'store.shipping_provider'           => ['required', 'string', 'in:table,melhorenvio'],
            'store.origin.name'                 => ['nullable', 'string', 'max:120'],
            'store.origin.street1'              => ['nullable', 'string', 'max:180'],
            'store.origin.street2'              => ['nullable', 'string', 'max:180'],
            'store.origin.city'                 => ['nullable', 'string', 'max:120'],
            'store.origin.postcode'             => ['nullable', 'string', 'max:40'],
            'store.origin.country'              => ['nullable', 'string', 'size:2'],
            'store.origin.phone'                => ['nullable', 'string', 'max:40'],
            'store.origin.email'                => ['nullable', 'email', 'max:120'],
            'store.melhorenvio.token'           => ['nullable', 'string', 'max:255'],
            'store.melhorenvio.base_url'        => ['nullable', 'url', 'max:255'],
        ]);

        StoreSetting::setMany($data);

        return back()->with('success', 'Configurações atualizadas com sucesso.');
    }

    public function updatePixels(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'store_pixel_facebook'              => ['nullable', 'string', 'max:20000'],
            'store_pixel_facebook_currency'     => ['required', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
        ]);

        StoreSetting::setMany([
            'store.pixel.facebook' => filled($data['store_pixel_facebook'] ?? null) ? trim((string) $data['store_pixel_facebook']) : null,
            'store.pixel.facebook_currency' => strtoupper((string) $data['store_pixel_facebook_currency']),
        ]);

        return back()->with('success', 'Configurações atualizadas com sucesso.');
    }

    private function settingsFor(array $keys): array
    {
        $saved = StoreSetting::getMany($keys);
        $settings = [];

        foreach ($keys as $key) {
            $settings[$key] = $saved[$key] ?? config($key);
        }

        return $settings;
    }
}
