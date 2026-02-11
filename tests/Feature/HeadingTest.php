<?php

declare(strict_types=1);

use PdfParse\FlatPdf\FlatPdf;
use PdfParse\FlatPdf\Style;

describe('Headings', function () {

    beforeEach(function () {
        $this->style = new Style(compress: false);
    });

    describe('h1()', function () {
        it('renders heading text in the PDF', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->h1('Title');
            $output = $pdf->output();
            expect(str_contains($output, '(Title)'))->toBeTrue();
        });

        it('draws an underline beneath the heading', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->h1('Title');
            $output = $pdf->output();
            expect(str_contains($output, 'l S'))->toBeTrue();
        });
    });

    describe('h2()', function () {
        it('renders heading text', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->h2('Subtitle');
            $output = $pdf->output();
            expect(str_contains($output, '(Subtitle)'))->toBeTrue();
        });
    });

    describe('h3()', function () {
        it('renders heading text', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->h3('Section');
            $output = $pdf->output();
            expect(str_contains($output, '(Section)'))->toBeTrue();
        });
    });

    it('renders all heading levels', function () {
        $pdf = new FlatPdf($this->style);
        $pdf->h1('H1');
        $pdf->h2('H2');
        $pdf->h3('H3');
        $output = $pdf->output();
        expect(str_contains($output, '(H1)'))->toBeTrue();
        expect(str_contains($output, '(H2)'))->toBeTrue();
        expect(str_contains($output, '(H3)'))->toBeTrue();
    });
});
