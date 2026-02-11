<?php

declare(strict_types=1);

use PdfParse\FlatPdf\FlatPdf;
use PdfParse\FlatPdf\Style;

describe('Page Management', function () {

    describe('initial state', function () {
        it('starts on page 1', function () {
            expect((new FlatPdf())->getCurrentPage())->toBe(1);
        });

        it('has remaining space equal to content height', function () {
            $style = new Style();
            $pdf = new FlatPdf($style);
            expect($pdf->getRemainingSpace())->toBe($style->contentHeight());
        });
    });

    describe('pageBreak()', function () {
        it('increments the page count', function () {
            $pdf = new FlatPdf();
            $pdf->pageBreak();
            expect($pdf->getCurrentPage())->toBe(2);
        });

        it('resets cursor to top of new page', function () {
            $style = new Style();
            $pdf = new FlatPdf($style);
            $pdf->text('Some content');
            $pdf->pageBreak();
            expect($pdf->getRemainingSpace())->toBe($style->contentHeight());
        });
    });

    describe('newPage()', function () {
        it('creates a new page and updates page count', function () {
            $pdf = new FlatPdf();
            $pdf->newPage();
            expect($pdf->getCurrentPage())->toBe(2);
        });
    });

    describe('space()', function () {
        it('reduces remaining space', function () {
            $style = new Style();
            $pdf = new FlatPdf($style);
            $initial = $pdf->getRemainingSpace();
            $pdf->space(50);
            expect($pdf->getRemainingSpace())->toBe($initial - 50);
        });

        it('triggers page break when space exceeds remaining', function () {
            $pdf = new FlatPdf();
            $pdf->space(10000);
            expect($pdf->getCurrentPage())->toBe(2);
        });
    });

    describe('hr()', function () {
        it('renders a horizontal rule', function () {
            $style = new Style(compress: false);
            $pdf = new FlatPdf($style);
            $pdf->hr();
            $output = $pdf->output();
            expect(str_contains($output, 'l S'))->toBeTrue();
        });

        it('reduces remaining space', function () {
            $style = new Style();
            $pdf = new FlatPdf($style);
            $before = $pdf->getRemainingSpace();
            $pdf->hr();
            expect($pdf->getRemainingSpace())->toBeLessThan($before);
        });
    });

    describe('automatic page breaks', function () {
        it('creates new pages when text exceeds page height', function () {
            $pdf = new FlatPdf();
            for ($i = 0; $i < 500; $i++) {
                $pdf->text("Line {$i} of a very long document.");
            }
            expect($pdf->getCurrentPage())->toBeGreaterThan(1);
        });
    });

    describe('header and footer rendering', function () {
        it('renders page numbers when enabled', function () {
            $style = new Style(compress: false, showPageNumbers: true);
            $pdf = new FlatPdf($style);
            $pdf->text('Content');
            $output = $pdf->output();
            expect(str_contains($output, 'Page 1 of 1'))->toBeTrue();
        });

        it('renders header text when set', function () {
            $style = new Style(compress: false, headerText: 'My Header');
            $pdf = new FlatPdf($style);
            $pdf->text('Content');
            $output = $pdf->output();
            expect(str_contains($output, '(My Header)'))->toBeTrue();
        });

        it('renders footer text when set', function () {
            $style = new Style(compress: false, footerText: 'Confidential');
            $pdf = new FlatPdf($style);
            $pdf->text('Content');
            $output = $pdf->output();
            expect(str_contains($output, '(Confidential)'))->toBeTrue();
        });

        it('replaces {page} and {pages} placeholders', function () {
            $style = new Style(
                compress: false,
                showPageNumbers: true,
                pageNumberFormat: '{page}/{pages}'
            );
            $pdf = new FlatPdf($style);
            $pdf->text('Content');
            $output = $pdf->output();
            expect(str_contains($output, '(1/1)'))->toBeTrue();
        });

        it('renders correct total page count across multiple pages', function () {
            $style = new Style(compress: false, showPageNumbers: true);
            $pdf = new FlatPdf($style);
            for ($i = 0; $i < 200; $i++) {
                $pdf->text("Line {$i}");
            }
            $output = $pdf->output();
            $pageCount = $pdf->getCurrentPage();
            expect(str_contains($output, "of {$pageCount}"))->toBeTrue();
        });

        it('resolves total page count with compression enabled', function () {
            $style = new Style(compress: true, showPageNumbers: true, pageNumberFormat: 'Page {page} of {pages}');
            $pdf = new FlatPdf($style);
            $rows = array_fill(0, 200, ['Data', 'More data']);
            $pdf->table(['A', 'B'], $rows);
            $output = $pdf->output();
            $pageCount = $pdf->getCurrentPage();
            expect($pageCount)->toBeGreaterThan(1);
            // The placeholder must not survive into the final output
            expect(str_contains($output, '___TOTAL_PAGES___'))->toBeFalse();
        });
    });
});
