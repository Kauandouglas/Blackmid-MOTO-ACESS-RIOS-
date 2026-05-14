<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Valor do frete padrão
    |--------------------------------------------------------------------------
    | Usado como fallback quando a cotacao em API nao estiver disponivel.
    */
    'shipping_fee' => (float) env('STORE_SHIPPING_FEE', 19.90),

    /*
    |--------------------------------------------------------------------------
    | Provider de cotação de frete
    |--------------------------------------------------------------------------
    | table        -> usa tabela local de fallback por peso
    | melhorenvio  -> usa Melhor Envio para consultar PAC e SEDEX dos Correios
    */
    'shipping_provider' => env('STORE_SHIPPING_PROVIDER', 'table'),

    /*
    |--------------------------------------------------------------------------
    | Endereço de origem da loja
    |--------------------------------------------------------------------------
    | Necessário para cotações reais via agregador.
    */
    'origin' => [
        'name'     => env('STORE_ORIGIN_NAME', env('APP_NAME', 'Origem Brasileira')),
        'street1'  => env('STORE_ORIGIN_STREET1', ''),
        'street2'  => env('STORE_ORIGIN_STREET2', ''),
        'city'     => env('STORE_ORIGIN_CITY', ''),
        'postcode' => env('STORE_ORIGIN_POSTCODE', ''),
        'country'  => env('STORE_ORIGIN_COUNTRY', 'BR'),
        'phone'    => env('STORE_ORIGIN_PHONE', ''),
        'email'    => env('STORE_ORIGIN_EMAIL', env('MAIL_FROM_ADDRESS', '')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Melhor Envio / Correios
    |--------------------------------------------------------------------------
    */
    'melhorenvio' => [
        'token' => env('MELHOR_ENVIO_TOKEN', ''),
        'base_url' => env('MELHOR_ENVIO_BASE_URL', 'https://www.melhorenvio.com.br'),
    ],

    'coupons' => [
        'BLACKMID10' => ['type' => 'percent', 'value' => 10],
        'PRIMEIRACOMPRA' => ['type' => 'percent', 'value' => 5],
    ],

    /*
    |--------------------------------------------------------------------------
    | Facebook Pixel
    |--------------------------------------------------------------------------
    */
    'pixel' => [
        'facebook' => env('STORE_PIXEL_FACEBOOK', ''),
        'facebook_currency' => env('STORE_PIXEL_FACEBOOK_CURRENCY', 'BRL'),
    ],

];
