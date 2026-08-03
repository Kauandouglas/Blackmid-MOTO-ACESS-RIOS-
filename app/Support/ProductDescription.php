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
        $description = (string) preg_replace(
            '/&(?:amp;)*lt;\s*br\s*\/?\s*&(?:amp;)*gt;/i',
            '<br>',
            $description ?? '',
        );

        $description = (string) preg_replace(
            '/&(?:amp;)*nbsp;|&#0*160;|&#x0*a0;/i',
            ' ',
            $description,
        );

        return str_replace("\u{00A0}", ' ', $description);
    }
}
