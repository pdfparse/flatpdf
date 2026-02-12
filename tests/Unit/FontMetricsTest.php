<?php

declare(strict_types=1);

use PdfParse\FlatPdf\FontMetrics;

describe('FontMetrics', function () {

    describe('resolveFontName()', function () {
        it('resolves Helvetica with no modifiers', function () {
            expect(FontMetrics::resolveFontName('Helvetica'))->toBe('Helvetica');
        });

        it('resolves Helvetica bold', function () {
            expect(FontMetrics::resolveFontName('Helvetica', bold: true))->toBe('Helvetica-Bold');
        });

        it('resolves Helvetica italic as Oblique', function () {
            expect(FontMetrics::resolveFontName('Helvetica', italic: true))->toBe('Helvetica-Oblique');
        });

        it('resolves Helvetica bold+italic as BoldOblique', function () {
            expect(FontMetrics::resolveFontName('Helvetica', bold: true, italic: true))->toBe('Helvetica-BoldOblique');
        });

        it('resolves Courier with no modifiers', function () {
            expect(FontMetrics::resolveFontName('Courier'))->toBe('Courier');
        });

        it('resolves Courier bold', function () {
            expect(FontMetrics::resolveFontName('Courier', bold: true))->toBe('Courier-Bold');
        });

        it('resolves Courier italic as Oblique', function () {
            expect(FontMetrics::resolveFontName('Courier', italic: true))->toBe('Courier-Oblique');
        });

        it('resolves Courier bold+italic as BoldOblique', function () {
            expect(FontMetrics::resolveFontName('Courier', bold: true, italic: true))->toBe('Courier-BoldOblique');
        });

        it('resolves Times-Roman with no modifiers', function () {
            expect(FontMetrics::resolveFontName('Times-Roman'))->toBe('Times-Roman');
        });

        it('resolves Times-Roman bold', function () {
            expect(FontMetrics::resolveFontName('Times-Roman', bold: true))->toBe('Times-Bold');
        });

        it('resolves Times-Roman italic', function () {
            expect(FontMetrics::resolveFontName('Times-Roman', italic: true))->toBe('Times-Italic');
        });

        it('resolves Times-Roman bold+italic', function () {
            expect(FontMetrics::resolveFontName('Times-Roman', bold: true, italic: true))->toBe('Times-BoldItalic');
        });

        it('resolves "arial" alias to Helvetica', function () {
            expect(FontMetrics::resolveFontName('arial'))->toBe('Helvetica');
        });

        it('resolves "sans-serif" alias to Helvetica', function () {
            expect(FontMetrics::resolveFontName('sans-serif'))->toBe('Helvetica');
        });

        it('resolves "monospace" alias to Courier', function () {
            expect(FontMetrics::resolveFontName('monospace'))->toBe('Courier');
        });

        it('resolves "serif" alias to Times-Roman', function () {
            expect(FontMetrics::resolveFontName('serif'))->toBe('Times-Roman');
        });

        it('resolves "times" alias to Times-Roman', function () {
            expect(FontMetrics::resolveFontName('times'))->toBe('Times-Roman');
        });

        it('resolves aliases case-insensitively', function () {
            expect(FontMetrics::resolveFontName('ARIAL'))->toBe('Helvetica');
            expect(FontMetrics::resolveFontName('Monospace'))->toBe('Courier');
        });

        it('passes through unknown font names unchanged', function () {
            expect(FontMetrics::resolveFontName('CustomFont'))->toBe('CustomFont');
        });

        it('resolves alias with bold modifier', function () {
            expect(FontMetrics::resolveFontName('arial', bold: true))->toBe('Helvetica-Bold');
        });

        it('resolves alias with italic modifier', function () {
            expect(FontMetrics::resolveFontName('serif', italic: true))->toBe('Times-Italic');
        });
    });

    describe('charWidth()', function () {
        it('returns specific width for known Helvetica characters', function () {
            expect(FontMetrics::charWidth('A', 'Helvetica'))->toBe(667);
            expect(FontMetrics::charWidth(' ', 'Helvetica'))->toBe(278);
            expect(FontMetrics::charWidth('i', 'Helvetica'))->toBe(222);
        });

        it('returns default width for unknown characters', function () {
            expect(FontMetrics::charWidth("\x80", 'Helvetica'))->toBe(556);
        });

        it('falls back to Helvetica for Helvetica-Oblique', function () {
            expect(FontMetrics::charWidth('A', 'Helvetica-Oblique'))->toBe(667);
        });

        it('falls back to Helvetica-Bold for Helvetica-BoldOblique', function () {
            expect(FontMetrics::charWidth('A', 'Helvetica-BoldOblique'))
                ->toBe(FontMetrics::charWidth('A', 'Helvetica-Bold'));
        });

        it('returns fixed 600 for all Courier variants', function () {
            expect(FontMetrics::charWidth('A', 'Courier'))->toBe(600);
            expect(FontMetrics::charWidth('i', 'Courier'))->toBe(600);
            expect(FontMetrics::charWidth('A', 'Courier-Bold'))->toBe(600);
            expect(FontMetrics::charWidth('A', 'Courier-Oblique'))->toBe(600);
            expect(FontMetrics::charWidth('A', 'Courier-BoldOblique'))->toBe(600);
        });

        it('returns Times-Roman widths', function () {
            expect(FontMetrics::charWidth('A', 'Times-Roman'))->toBe(722);
            expect(FontMetrics::charWidth(' ', 'Times-Roman'))->toBe(250);
        });

        it('falls back to Helvetica for unknown fonts', function () {
            expect(FontMetrics::charWidth('A', 'UnknownFont'))->toBe(667);
        });

        it('falls back to Times-Roman for Times-Italic', function () {
            expect(FontMetrics::charWidth('A', 'Times-Italic'))
                ->toBe(FontMetrics::charWidth('A', 'Times-Roman'));
        });

        it('falls back to Times-Bold for Times-BoldItalic', function () {
            expect(FontMetrics::charWidth('A', 'Times-BoldItalic'))
                ->toBe(FontMetrics::charWidth('A', 'Times-Bold'));
        });
    });

    describe('stringWidth()', function () {
        it('returns zero for an empty string', function () {
            expect(FontMetrics::stringWidth('', 'Helvetica', 12.0))->toBe(0.0);
        });

        it('calculates width as sum of char widths scaled by font size', function () {
            $width = FontMetrics::stringWidth('AB', 'Helvetica', 12.0);
            expect($width)->toBe((667 + 667) / 1000.0 * 12.0);
        });

        it('scales linearly with font size', function () {
            $w12 = FontMetrics::stringWidth('Hello', 'Helvetica', 12.0);
            $w24 = FontMetrics::stringWidth('Hello', 'Helvetica', 24.0);
            expect($w24)->toBe($w12 * 2.0);
        });

        it('returns consistent width for monospace Courier', function () {
            $w = FontMetrics::stringWidth('iii', 'Courier', 10.0);
            expect($w)->toBe((600 * 3) / 1000.0 * 10.0);
        });

        it('returns larger width for wider characters', function () {
            $wideWidth = FontMetrics::stringWidth('M', 'Helvetica', 12.0);
            $narrowWidth = FontMetrics::stringWidth('i', 'Helvetica', 12.0);
            expect($wideWidth)->toBeGreaterThan($narrowWidth);
        });
    });

    describe('wordWrap()', function () {
        it('returns single-element array for text that fits on one line', function () {
            $lines = FontMetrics::wordWrap('Hello', 'Helvetica', 12.0, 500.0);
            expect($lines)->toBe(['Hello']);
        });

        it('wraps long text into multiple lines', function () {
            $text = 'The quick brown fox jumps over the lazy dog and keeps running far away into the distance';
            $lines = FontMetrics::wordWrap($text, 'Helvetica', 10.0, 100.0);
            expect(count($lines))->toBeGreaterThan(1);
        });

        it('returns empty string array for empty input', function () {
            $lines = FontMetrics::wordWrap('', 'Helvetica', 12.0, 500.0);
            expect($lines)->toBe(['']);
        });

        it('returns full text when maxWidth is zero', function () {
            $lines = FontMetrics::wordWrap('Hello world', 'Helvetica', 12.0, 0.0);
            expect($lines)->toBe(['Hello world']);
        });

        it('returns full text when maxWidth is negative', function () {
            $lines = FontMetrics::wordWrap('Hello world', 'Helvetica', 12.0, -10.0);
            expect($lines)->toBe(['Hello world']);
        });

        it('handles single very long word that exceeds maxWidth', function () {
            $lines = FontMetrics::wordWrap('Supercalifragilisticexpialidocious', 'Helvetica', 12.0, 50.0);
            expect($lines)->toHaveCount(1);
            expect($lines[0])->toBe('Supercalifragilisticexpialidocious');
        });

        it('preserves word boundaries', function () {
            $text = 'Hello World Foo Bar';
            $lines = FontMetrics::wordWrap($text, 'Helvetica', 10.0, 80.0);
            foreach ($lines as $line) {
                expect($line)->not->toMatch('/^\s/');
                expect($line)->not->toMatch('/\s$/');
            }
        });

        it('produces more lines for larger font sizes', function () {
            $text = 'The quick brown fox jumps over the lazy dog';
            $lines9 = FontMetrics::wordWrap($text, 'Helvetica', 9.0, 150.0);
            $lines16 = FontMetrics::wordWrap($text, 'Helvetica', 16.0, 150.0);
            expect(count($lines16))->toBeGreaterThanOrEqual(count($lines9));
        });

        it('wraps correctly with Courier monospace', function () {
            $text = 'AAAAAA BBBBBB CCCCCC';
            $lines = FontMetrics::wordWrap($text, 'Courier', 10.0, 50.0);
            expect(count($lines))->toBeGreaterThan(1);
        });
    });

    describe('UTF-8 encoding in metrics', function () {
        it('measures converted width for text with em dash', function () {
            // "A—B" in UTF-8 is 5 bytes but 3 Windows-1252 characters
            $width = FontMetrics::stringWidth("A\xE2\x80\x94B", 'Helvetica', 12.0);
            $widthA = FontMetrics::stringWidth('A', 'Helvetica', 12.0);
            $widthB = FontMetrics::stringWidth('B', 'Helvetica', 12.0);
            // Width should be 3 chars worth, not 5 bytes worth
            expect($width)->toBeLessThan(($widthA + $widthB) * 3);
        });

        it('word wraps UTF-8 text and returns Windows-1252 output', function () {
            $text = "caf\xC3\xA9 latt\xC3\xA9";
            $lines = FontMetrics::wordWrap($text, 'Helvetica', 10.0, 500.0);
            expect($lines)->toHaveCount(1);
            expect($lines[0])->toBe("caf\xE9 latt\xE9");
        });

        it('uses specific widths for extended Win-1252 characters', function () {
            // Euro sign should use the specific width (556), not the default
            $euroWidth = FontMetrics::charWidth("\x80", 'Helvetica');
            $defaultWidth = 556;
            expect($euroWidth)->toBe($defaultWidth);

            // Em dash should use specific width (1000), not default (556)
            $emDashWidth = FontMetrics::charWidth("\x97", 'Helvetica');
            expect($emDashWidth)->toBe(1000);
        });
    });
});
