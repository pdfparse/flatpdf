<?php

declare(strict_types=1);

use PdfParse\FlatPdf\FlatPdf;
use PdfParse\FlatPdf\Style;

describe('Text Rendering', function () {

    beforeEach(function () {
        $this->style = new Style(compress: false);
    });

    describe('text()', function () {
        it('renders text into the PDF stream', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->text('Hello World');
            $output = $pdf->output();
            expect(str_contains($output, '(Hello World)'))->toBeTrue();
        });

        it('escapes special PDF characters', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->text('Price is (100) dollars\\half');
            $output = $pdf->output();
            expect(str_contains($output, '\\(100\\)'))->toBeTrue();
            expect(str_contains($output, '\\\\half'))->toBeTrue();
        });

        it('wraps long text across multiple lines', function () {
            $pdf = new FlatPdf($this->style);
            $longText = str_repeat('Word ', 200);
            $pdf->text($longText);
            $output = $pdf->output();
            $tjCount = substr_count($output, 'Tj ET');
            expect($tjCount)->toBeGreaterThan(1);
        });

        it('handles multi-paragraph text with newlines', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->text("Paragraph one.\n\nParagraph two.");
            $output = $pdf->output();
            expect(str_contains($output, '(Paragraph one.)'))->toBeTrue();
            expect(str_contains($output, '(Paragraph two.)'))->toBeTrue();
        });

        it('handles empty string without error', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->text('');
            $output = $pdf->output();
            expect(str_starts_with($output, '%PDF-1.4'))->toBeTrue();
        });

        it('accepts custom font, size, and color', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->text('Custom', 'Courier', 14.0, [1.0, 0.0, 0.0]);
            $output = $pdf->output();
            expect(str_contains($output, '(Custom)'))->toBeTrue();
            expect(str_contains($output, '1 0 0 rg'))->toBeTrue();
        });

        it('renders ASCII dashes without garbled multi-byte output', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->text('Acme Corp - Q4 Report');
            $output = $pdf->output();
            expect(str_contains($output, '(Acme Corp - Q4 Report)'))->toBeTrue();
            // UTF-8 em dash bytes (0xE2 0x80 0x94) must not appear in the stream
            expect(str_contains($output, "\xE2\x80\x94"))->toBeFalse();
        });
    });

    describe('UTF-8 encoding conversion', function () {
        it('converts UTF-8 em dash to single byte in PDF stream', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->text("Value \xE2\x80\x94 100");
            $output = $pdf->output();
            expect(str_contains($output, "\xE2\x80\x94"))->toBeFalse();
            expect(str_contains($output, "\x97"))->toBeTrue();
        });

        it('converts UTF-8 Euro sign in PDF stream', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->text("\xE2\x82\xAC" . "500");
            $output = $pdf->output();
            expect(str_contains($output, "\x80" . "500"))->toBeTrue();
        });

        it('converts superscript characters in PDF stream', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->text("Area (km\xC2\xB2)");
            $output = $pdf->output();
            expect(str_contains($output, "km\xB2"))->toBeTrue();
            expect(str_contains($output, "\xC2\xB2"))->toBeFalse();
        });

        it('handles accented characters in headings', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->h1("Caf\xC3\xA9 Report");
            $output = $pdf->output();
            expect(str_contains($output, "Caf\xE9 Report"))->toBeTrue();
        });

        it('handles UTF-8 text in table cells', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->table(
                ['Item', 'Price'],
                [["Widget\xE2\x84\xA2", "\xE2\x82\xAC" . "10.00"]]
            );
            $output = $pdf->output();
            expect(str_contains($output, "Widget\x99"))->toBeTrue();
            expect(str_contains($output, "\x80" . "10.00"))->toBeTrue();
        });

        it('handles UTF-8 text in header and footer', function () {
            $style = new Style(
                compress: false,
                headerText: "\xC2\xA9 2024 Acme",
                footerText: "Confidential \xE2\x80\x94 Internal",
            );
            $pdf = new FlatPdf($style);
            $pdf->text('Content');
            $output = $pdf->output();
            expect(str_contains($output, "\xA9 2024 Acme"))->toBeTrue();
            expect(str_contains($output, "Confidential \x97 Internal"))->toBeTrue();
        });
    });

    describe('bold()', function () {
        it('renders text using the bold font variant', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->bold('Bold text');
            $output = $pdf->output();
            expect(str_contains($output, '(Bold text)'))->toBeTrue();
        });
    });

    describe('italic()', function () {
        it('renders text using the italic/oblique font variant', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->italic('Italic text');
            $output = $pdf->output();
            expect(str_contains($output, '(Italic text)'))->toBeTrue();
        });
    });

    describe('code()', function () {
        it('renders text in Courier monospace', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->code('var_dump($x);');
            $output = $pdf->output();
            expect(str_contains($output, '\\($x\\)'))->toBeTrue();
        });
    });
});
