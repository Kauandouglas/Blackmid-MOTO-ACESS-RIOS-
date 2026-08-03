<?php

namespace App\Support;

final class ProductDescription
{
    /**
     * Convert line-break tags escaped by external catalog integrations back to HTML.
     * Other encoded tags remain encoded so they cannot unexpectedly become executable markup.
     */
    public static function forDisplay(?string $description): string
    {
        return (string) preg_replace(
            '/&(?:amp;)*lt;\s*br\s*\/?\s*&(?:amp;)*gt;/i',
            '<br>',
            $description ?? '',
        );
    }
}
