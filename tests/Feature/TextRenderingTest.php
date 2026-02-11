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
