<?php

namespace Tests\Unit;

use App\Support\ProductDescription;
use PHPUnit\Framework\TestCase;

class ProductDescriptionTest extends TestCase
{
    public function test_it_restores_escaped_line_breaks_from_imported_descriptions(): void
    {
        $description = 'Primeira linha&lt;br&gt;Segunda linha&lt;br /&gt;Terceira linha';

        $this->assertSame(
            'Primeira linha<br>Segunda linha<br>Terceira linha',
            ProductDescription::forDisplay($description),
        );
    }

    public function test_it_handles_repeated_encoding_without_decoding_other_tags(): void
    {
        $description = 'Texto&amp;lt;br&amp;gt;Nova linha &lt;script&gt;alert(1)&lt;/script&gt;';

        $this->assertSame(
            'Texto<br>Nova linha &lt;script&gt;alert(1)&lt;/script&gt;',
            ProductDescription::forDisplay($description),
        );
    }
}
