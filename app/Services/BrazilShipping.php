<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class BrazilShipping
{
    private const PACKAGING_G = 150;

    private array $fallbackRates = [
        'pac' => [
            500 => 25.94,
            1000 => 29.90,
            2000 => 36.90,
            5000 => 52.90,
            10000 => 79.90,
        ],
        'sedex' => [
            500 => 48.15,
            1000 => 56.90,
            2000 => 72.90,
            5000 => 119.90,
            10000 => 169.90,
        ],
    ];

    private array $labels = [
        'pac' => 'PAC (prazo apos data de postagem)',
        'sedex' => 'SEDEX (prazo apos data de postagem)',
    ];

    private array $eta = [
        'pac' => '8 dias uteis',
        'sedex' => '5 dias uteis',
    ];

    public function getQuote(int $productWeightGrams, float $subtotal = 0, array $destination = []): array
    {
        $resolved = $this->resolveRates($productWeightGrams, $subtotal, $destination);
        $rates = $resolved['rates'];
        $default = $rates['pac'] ?? reset($rates);
        $shipping = $default['is_free'] ? 0 : (float) $default['price'];

        return [
            'source' => $resolved['source'],
            'rates' => $rates,
            'default' => $default,
            'shipping' => $shipping,
            'total' => $subtotal + $shipping,
            'subtotal' => $subtotal,
            'has_api' => $this->usesMelhorEnvio(),
        ];
    }

    public function getPrice(string $service, int $productWeightGrams, float $subtotal = 0, array $destination = []): float
    {
        $rates = $this->getQuote($productWeightGrams, $subtotal, $destination)['rates'];
        $rate = $rates[$service] ?? $rates['pac'] ?? reset($rates);

        return $rate['is_free'] ? 0.0 : (float) $rate['price'];
    }

    private function resolveRates(int $productWeightGrams, float $subtotal, array $destination): array
    {
        if ($this->usesMelhorEnvio() && $this->hasDestinationForApi($destination)) {
            try {
                return [
                    'source' => 'api',
                    'rates' => $this->getRatesFromMelhorEnvio($productWeightGrams, $subtotal, $destination),
                ];
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return [
            'source' => 'fallback',
            'rates' => $this->getFallbackRates($productWeightGrams, $subtotal),
        ];
    }

    private function usesMelhorEnvio(): bool
    {
        return config('store.shipping_provider') === 'melhorenvio'
            && filled(config('store.melhorenvio.token'));
    }

    private function hasDestinationForApi(array $destination): bool
    {
        return filled($destination['postcode'] ?? null);
    }

    private function getFallbackRates(int $productWeightGrams, float $subtotal = 0): array
    {
        $shipWeight = $productWeightGrams + self::PACKAGING_G;
        $result = [];

        foreach ($this->fallbackRates as $service => $table) {
            $price = $this->lookupFallback($table, $shipWeight);

            $result[$service] = [
                'service' => $service,
                'name' => $this->labels[$service],
                'eta' => $this->eta[$service],
                'price' => $price,
                'is_free' => false,
            ];
        }

        return $result;
    }

    private function getRatesFromMelhorEnvio(int $productWeightGrams, float $subtotal, array $destination): array
    {
        $originCep = preg_replace('/\D+/', '', (string) config('store.origin.postcode'));
        $destinationCep = preg_replace('/\D+/', '', (string) ($destination['postcode'] ?? ''));

        if ($originCep === '' || $destinationCep === '') {
            throw new RuntimeException('CEP de origem ou destino ausente.');
        }

        $grams = max(300, $productWeightGrams + self::PACKAGING_G);
        $response = Http::withToken((string) config('store.melhorenvio.token'))
            ->acceptJson()
            ->asJson()
            ->post(rtrim((string) config('store.melhorenvio.base_url'), '/') . '/api/v2/me/shipment/calculate', [
                'from' => ['postal_code' => $originCep],
                'to' => ['postal_code' => $destinationCep],
                'package' => [
                    'height' => 8,
                    'width' => 20,
                    'length' => 28,
                    'weight' => round($grams / 1000, 3),
                ],
                'options' => [
                    'receipt' => false,
                    'own_hand' => false,
                    'insurance_value' => max(1, $subtotal),
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Falha ao consultar frete no Melhor Envio.');
        }

        $mapped = collect($response->json() ?? [])
            ->map(fn (array $rate) => $this->mapMelhorEnvioRate($rate, $subtotal))
            ->filter()
            ->keyBy('service')
            ->all();

        if (empty($mapped)) {
            throw new RuntimeException('Nenhuma tarifa de frete retornada.');
        }

        return array_replace($this->getFallbackRates($productWeightGrams, $subtotal), $mapped);
    }

    private function mapMelhorEnvioRate(array $rate, float $subtotal): ?array
    {
        if (isset($rate['error'])) {
            return null;
        }

        $company = mb_strtolower((string) data_get($rate, 'company.name', ''));
        $name = mb_strtolower((string) ($rate['name'] ?? ''));
        $service = null;

        if (str_contains($company, 'correios') && str_contains($name, 'pac')) {
            $service = 'pac';
        } elseif (str_contains($company, 'correios') && str_contains($name, 'sedex')) {
            $service = 'sedex';
        }

        if (! $service) {
            return null;
        }

        $deliveryTime = (int) ($rate['delivery_time'] ?? 0);

        return [
            'service' => $service,
            'name' => $this->labels[$service],
            'eta' => $deliveryTime > 0 ? $deliveryTime . ' dias uteis' : $this->eta[$service],
            'price' => (float) ($rate['price'] ?? $rate['custom_price'] ?? 0),
            'is_free' => false,
        ];
    }

    private function lookupFallback(array $table, int $grams): float
    {
        foreach ($table as $maxGrams => $price) {
            if ($grams <= $maxGrams) {
                return (float) $price;
            }
        }

        return (float) end($table);
    }
}
