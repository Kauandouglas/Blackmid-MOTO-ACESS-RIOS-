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

    /**
     * Bling descriptions for single-size SKUs often bake in a literal "Tamanho: NN"
     * line. Once a product is merged into a multi-size listing that line is stale
     * (it never matches whatever size the shopper has selected), so drop it.
     */
    public static function withoutSizeMention(?string $description): ?string
    {
        $description = (string) ($description ?? '');

        if ($description === '') {
            return null;
        }

        $pattern = '/(?:(?:&(?:amp;)*lt;\s*br\s*\/?\s*&(?:amp;)*gt;)|<br\s*\/?>)?\s*Tamanho\s*:\s*[^<\r\n]*/iu';

        $cleaned = trim((string) preg_replace($pattern, '', $description));

        return $cleaned !== '' ? $cleaned : null;
    }

    /**
     * Bling descriptions for single-size SKUs often open with the product's
     * full original name (including its size), e.g. "Capacete X 56<br>Cor: ...".
     * Once merged into a multi-size product that leading line just repeats the
     * title (now stale, since it still names one old size), so drop it.
     */
    public static function withoutLeadingTitleRepeat(?string $description, string $productName): ?string
    {
        $description = (string) ($description ?? '');
        $productName = trim($productName);

        if ($description === '' || $productName === '') {
            return $description !== '' ? $description : null;
        }

        $breakPattern = '(?:&(?:amp;)*lt;\s*br\s*\/?\s*&(?:amp;)*gt;|<br\s*\/?>|\r\n|\r|\n)';
        $pattern = '/^\s*'.preg_quote($productName, '/').'(?:\s+\S+)?\s*(?:'.$breakPattern.')+\s*/iu';

        $cleaned = trim((string) preg_replace($pattern, '', $description, 1));

        return $cleaned !== '' ? $cleaned : null;
    }

    /**
     * Runs the full cleanup used once a product covers more than one size:
     * drops both the stale leading title+size line and the "Tamanho: NN" line.
     */
    public static function forMultiSizeProduct(?string $description, string $productName): ?string
    {
        $description = self::withoutLeadingTitleRepeat($description, $productName);

        return self::withoutSizeMention($description);
    }
}
