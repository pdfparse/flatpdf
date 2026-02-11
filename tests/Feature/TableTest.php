<?php

declare(strict_types=1);

use PdfParse\FlatPdf\FlatPdf;
use PdfParse\FlatPdf\Style;

describe('Tables', function () {

    beforeEach(function () {
        $this->style = new Style(compress: false);
    });

    describe('table()', function () {
        it('renders a basic table with headers and rows', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->table(['Name', 'Age'], [['Alice', '30'], ['Bob', '25']]);
            $output = $pdf->output();
            expect(str_contains($output, '(Name)'))->toBeTrue();
            expect(str_contains($output, '(Age)'))->toBeTrue();
            expect(str_contains($output, '(Alice)'))->toBeTrue();
            expect(str_contains($output, '(Bob)'))->toBeTrue();
        });

        it('renders a table with a single row', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->table(['Col'], [['Value']]);
            $output = $pdf->output();
            expect(str_contains($output, '(Col)'))->toBeTrue();
            expect(str_contains($output, '(Value)'))->toBeTrue();
        });

        it('handles empty rows array', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->table(['Name'], []);
            $output = $pdf->output();
            expect(str_contains($output, '(Name)'))->toBeTrue();
            expect(str_starts_with($output, '%PDF-1.4'))->toBeTrue();
        });

        it('handles cells with special characters', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->table(['Col'], [['Price: $100 (discount)']]);
            $output = $pdf->output();
            expect(str_contains($output, '\\(discount\\)'))->toBeTrue();
        });

        it('respects columnAligns option', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->table(['Left', 'Right'], [['a', 'b']], [
                'columnAligns' => ['left', 'right'],
            ]);
            $output = $pdf->output();
            expect(str_starts_with($output, '%PDF-1.4'))->toBeTrue();
        });

        it('respects explicit columnWidths option', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->table(['A', 'B'], [['1', '2']], [
                'columnWidths' => [200.0, 312.0],
            ]);
            $output = $pdf->output();
            expect(str_starts_with($output, '%PDF-1.4'))->toBeTrue();
        });

        it('respects fontSize option', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->table(['A'], [['1']], ['fontSize' => 12.0]);
            $output = $pdf->output();
            expect(str_contains($output, '12 Tf'))->toBeTrue();
        });

        it('triggers page break for large tables', function () {
            $pdf = new FlatPdf($this->style);
            $rows = array_fill(0, 200, ['Data', 'More data', 'Even more']);
            $pdf->table(['A', 'B', 'C'], $rows);
            expect($pdf->getCurrentPage())->toBeGreaterThan(1);
        });

        it('repeats headers on new pages by default', function () {
            $style = new Style(compress: false, tableRepeatHeaderOnNewPage: true);
            $pdf = new FlatPdf($style);
            $rows = array_fill(0, 200, ['X', 'Y']);
            $pdf->table(['Header1', 'Header2'], $rows);
            $output = $pdf->output();
            $count = substr_count($output, '(Header1)');
            expect($count)->toBeGreaterThan(1);
        });

        it('does not repeat headers when repeatHeader is false', function () {
            $style = new Style(compress: false);
            $pdf = new FlatPdf($style);
            $rows = array_fill(0, 200, ['X', 'Y']);
            $pdf->table(['UniqueHdr', 'Col2'], $rows, ['repeatHeader' => false]);
            $output = $pdf->output();
            $count = substr_count($output, '(UniqueHdr)');
            expect($count)->toBe(1);
        });

        it('auto-sizes columns based on content', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->table(
                ['Short', 'A Much Longer Column Header'],
                [['a', 'b'], ['c', 'A very long cell value that should cause wrapping']]
            );
            $output = $pdf->output();
            expect(str_starts_with($output, '%PDF-1.4'))->toBeTrue();
        });

        it('handles row with missing cells gracefully', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->table(['A', 'B', 'C'], [['only one']]);
            $output = $pdf->output();
            expect(str_starts_with($output, '%PDF-1.4'))->toBeTrue();
        });

        it('renders striped rows when enabled', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->table(['A'], [['1'], ['2'], ['3']], ['striped' => true]);
            $output = $pdf->output();
            // Alternate row background color should appear
            expect(str_contains($output, '0.95 0.96 0.98 rg'))->toBeTrue();
        });

        it('emits text color for every row after background fill', function () {
            $style = new Style(compress: false, textColor: [0.2, 0.2, 0.2]);
            $pdf = new FlatPdf($style);
            $pdf->table(['Name'], [['Alice'], ['Bob'], ['Charlie']]);
            $output = $pdf->output();

            // Each row's text must have its color re-emitted after drawRect
            // changes the fill color for the row background. Count text color
            // emissions — should be at least once per data row (3).
            $textColorCount = substr_count($output, '0.2 0.2 0.2 rg');
            expect($textColorCount)->toBeGreaterThanOrEqual(3);

            // All three row values must be present in the output
            expect(str_contains($output, '(Alice)'))->toBeTrue();
            expect(str_contains($output, '(Bob)'))->toBeTrue();
            expect(str_contains($output, '(Charlie)'))->toBeTrue();
        });

        it('renders with center alignment', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->table(['A', 'B'], [['1', '2']], [
                'columnAligns' => ['center', 'center'],
            ]);
            $output = $pdf->output();
            expect(str_starts_with($output, '%PDF-1.4'))->toBeTrue();
        });
    });

    describe('dataTable()', function () {
        it('renders a table from associative arrays', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->dataTable([
                ['name' => 'Alice', 'age' => '30'],
                ['name' => 'Bob', 'age' => '25'],
            ]);
            $output = $pdf->output();
            expect(str_contains($output, '(Alice)'))->toBeTrue();
        });

        it('converts underscore keys to title case for headers', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->dataTable([['first_name' => 'A']]);
            $output = $pdf->output();
            expect(str_contains($output, '(First name)'))->toBeTrue();
        });

        it('uses column labels when provided', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->dataTable(
                [['first_name' => 'Alice']],
                ['columnLabels' => ['first_name' => 'Full Name']]
            );
            $output = $pdf->output();
            expect(str_contains($output, '(Full Name)'))->toBeTrue();
        });

        it('applies formatters to values', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->dataTable(
                [['amount' => 1000]],
                ['formatters' => ['amount' => fn($v) => '$' . number_format((int) $v)]]
            );
            $output = $pdf->output();
            expect(str_contains($output, '($1,000)'))->toBeTrue();
        });

        it('selects specific columns when provided', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->dataTable(
                [['name' => 'Alice', 'age' => '30', 'secret' => 'hidden']],
                ['columns' => ['name', 'age']]
            );
            $output = $pdf->output();
            expect(str_contains($output, '(Alice)'))->toBeTrue();
            expect(str_contains($output, '(hidden)'))->toBeFalse();
        });

        it('returns early for empty data without error', function () {
            $pdf = new FlatPdf($this->style);
            $pdf->dataTable([]);
            $output = $pdf->output();
            expect(str_starts_with($output, '%PDF-1.4'))->toBeTrue();
        });
    });

    describe('summaryRow()', function () {
        it('renders a bold summary row', function () {
            $pdf = new FlatPdf($this->style);
            $colWidths = [100.0, 100.0, 100.0];
            $pdf->table(['A', 'B', 'C'], [['1', '2', '3']], ['columnWidths' => $colWidths]);
            $pdf->summaryRow(['Total', '', '$100'], $colWidths);
            $output = $pdf->output();
            expect(str_contains($output, '(Total)'))->toBeTrue();
            expect(str_contains($output, '($100)'))->toBeTrue();
        });
    });
});
