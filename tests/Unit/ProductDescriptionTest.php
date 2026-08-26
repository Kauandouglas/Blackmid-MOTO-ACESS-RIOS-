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

    public function test_it_converts_non_breaking_spaces_to_wrappable_spaces(): void
    {
        $description = "Uma&nbsp;linha&amp;nbsp;longa\u{00A0}demais&#160;para&#xA0;o card";

        $this->assertSame(
            'Uma linha longa demais para o card',
            ProductDescription::forDisplay($description),
        );
    }

    public function test_it_removes_a_stale_tamanho_line_from_a_merged_product_description(): void
    {
        $description = 'Capacete Hjc C10 Fabio Quartararo 2024 Verm Pret Branc 56 Cor: Vermelho&lt;br&gt;Tamanho: 56';

        $this->assertSame(
            'Capacete Hjc C10 Fabio Quartararo 2024 Verm Pret Branc 56 Cor: Vermelho',
            ProductDescription::withoutSizeMention($description),
        );
    }

    public function test_it_removes_a_stale_tamanho_line_rendered_as_real_br_tags(): void
    {
        $description = "Cor: Vermelho<br>Tamanho: 56<br>Mais detalhes do produto.";

        $this->assertSame(
            'Cor: Vermelho<br>Mais detalhes do produto.',
            ProductDescription::withoutSizeMention($description),
        );
    }

    public function test_it_returns_null_when_nothing_is_left_after_stripping(): void
    {
        $this->assertNull(ProductDescription::withoutSizeMention('Tamanho: 56'));
        $this->assertNull(ProductDescription::withoutSizeMention(null));
    }

    public function test_it_removes_a_leading_title_that_repeats_the_product_name_and_old_size(): void
    {
        $description = "Capacete Hjc I91 Bina Branc Verm Azu 56\n\n\nCor: Branco";

        $this->assertSame(
            'Cor: Branco',
            ProductDescription::withoutLeadingTitleRepeat($description, 'Capacete Hjc I91 Bina Branc Verm Azu'),
        );
    }

    public function test_it_leaves_descriptions_alone_when_they_do_not_repeat_the_title(): void
    {
        $description = "LUVAS LIBERTY\nPara seguranca, conforto e estilo.";

        $this->assertSame(
            $description,
            ProductDescription::withoutLeadingTitleRepeat($description, 'Luvas Texx Liberty Pret'),
        );
    }

    public function test_for_multi_size_product_strips_both_the_leading_title_and_the_tamanho_line(): void
    {
        $description = "Capacete Hjc I91 Carst Pret Cinz  58\n\n\nCor: Preto E Cinza<br>Tamanho: 58";

        $this->assertSame(
            'Cor: Preto E Cinza',
            ProductDescription::forMultiSizeProduct($description, 'Capacete Hjc I91 Carst Pret Cinz'),
        );
    }
}
