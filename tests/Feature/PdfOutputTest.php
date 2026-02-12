<?php

declare(strict_types=1);

use PdfParse\FlatPdf\FlatPdf;
use PdfParse\FlatPdf\Style;

describe('PDF Output', function () {

    describe('make()', function () {
        it('creates a FlatPdf instance with no arguments', function () {
            $pdf = FlatPdf::make();
            expect($pdf)->toBeInstanceOf(FlatPdf::class);
            expect($pdf->getCurrentPage())->toBe(1);
        });

        it('creates a FlatPdf instance with a style', function () {
            $pdf = FlatPdf::make(Style::compact());
            expect($pdf)->toBeInstanceOf(FlatPdf::class);
            expect($pdf->getCurrentPage())->toBe(1);
        });

        it('produces identical output to constructor', function () {
            $style = new Style(compress: false);

            $fromNew = new FlatPdf($style);
            $fromNew->text('Hello');

            $fromMake = FlatPdf::make($style);
            $fromMake->text('Hello');

            expect($fromMake->output())->toBe($fromNew->output());
        });
    });

    describe('output()', function () {
        it('produces valid PDF header', function () {
            $output = (new FlatPdf())->output();
            expect(str_starts_with($output, '%PDF-1.4'))->toBeTrue();
        });

        it('produces binary header marker', function () {
            $output = (new FlatPdf())->output();
            expect(str_contains($output, "\xE2\xE3\xCF\xD3"))->toBeTrue();
        });

        it('ends with %%EOF marker', function () {
            $output = (new FlatPdf())->output();
            expect(str_ends_with(trim($output), '%%EOF'))->toBeTrue();
        });

        it('contains proper PDF structure sections', function () {
            $output = (new FlatPdf())->output();
            expect(str_contains($output, 'xref'))->toBeTrue();
            expect(str_contains($output, 'trailer'))->toBeTrue();
            expect(str_contains($output, 'startxref'))->toBeTrue();
        });

        it('contains at least one page', function () {
            $output = (new FlatPdf())->output();
            expect(str_contains($output, '/Type /Page'))->toBeTrue();
        });

        it('includes font resources', function () {
            $output = (new FlatPdf())->output();
            expect(str_contains($output, '/Type /Font'))->toBeTrue();
            expect(str_contains($output, '/BaseFont /Helvetica'))->toBeTrue();
        });

        it('produces compressed output by default', function () {
            $output = (new FlatPdf())->output();
            expect(str_contains($output, '/FlateDecode'))->toBeTrue();
        });

        it('produces uncompressed output when compress is false', function () {
            $pdf = new FlatPdf(new Style(compress: false));
            $pdf->text('Hello');
            $output = $pdf->output();
            expect(str_contains($output, '/FlateDecode'))->toBeFalse();
            expect(str_contains($output, '(Hello)'))->toBeTrue();
        });

        it('compressed output is smaller than uncompressed for same content', function () {
            $rows = array_fill(0, 50, ['A', 'B', 'C']);

            $compressed = new FlatPdf(new Style(compress: true));
            $compressed->table(['X', 'Y', 'Z'], $rows);
            $compSize = strlen($compressed->output());

            $uncompressed = new FlatPdf(new Style(compress: false));
            $uncompressed->table(['X', 'Y', 'Z'], $rows);
            $uncompSize = strlen($uncompressed->output());

            expect($compSize)->toBeLessThan($uncompSize);
        });

        it('is idempotent (calling output() twice returns same result)', function () {
            $pdf = new FlatPdf();
            $pdf->text('Test');
            $out1 = $pdf->output();
            $out2 = $pdf->output();
            expect($out1)->toBe($out2);
        });
    });

    describe('save()', function () {
        it('writes the PDF to a file', function () {
            $tmpFile = tempnam(sys_get_temp_dir(), 'flatpdf_test_') . '.pdf';
            $pdf = new FlatPdf();
            $pdf->text('Save test');
            $pdf->save($tmpFile);

            expect(file_exists($tmpFile))->toBeTrue();
            $content = file_get_contents($tmpFile);
            expect(str_starts_with($content, '%PDF-1.4'))->toBeTrue();

            unlink($tmpFile);
        });

        it('produces the same bytes as output()', function () {
            $tmpFile = tempnam(sys_get_temp_dir(), 'flatpdf_test_') . '.pdf';
            $pdf = new FlatPdf();
            $pdf->text('Consistency test');
            $pdf->save($tmpFile);

            $fromSave = file_get_contents($tmpFile);
            $fromOutput = $pdf->output();
            expect($fromSave)->toBe($fromOutput);

            unlink($tmpFile);
        });
    });

    describe('complex document', function () {
        it('generates a multi-section document without errors', function () {
            $style = new Style(
                headerText: 'Test Report',
                footerText: 'Confidential',
                showPageNumbers: true,
            );
            $pdf = new FlatPdf($style);

            $pdf->h1('Section 1');
            $pdf->text('Introduction paragraph.');
            $pdf->h2('Subsection');
            $pdf->bold('Important note.');
            $pdf->italic('Additional detail.');
            $pdf->hr();
            $pdf->code('echo "hello";');
            $pdf->space(20);

            $pdf->h1('Section 2: Table');
            $pdf->table(
                ['Name', 'Value', 'Status'],
                array_fill(0, 100, ['Item', '$1,000', 'Active']),
                ['columnAligns' => ['left', 'right', 'center']]
            );

            $pdf->h1('Section 3: Data Table');
            $pdf->dataTable([
                ['id' => 1, 'name' => 'Alpha', 'score' => 95],
                ['id' => 2, 'name' => 'Beta', 'score' => 88],
            ]);

            $output = $pdf->output();
            expect(str_starts_with($output, '%PDF-1.4'))->toBeTrue();
            expect($pdf->getCurrentPage())->toBeGreaterThan(1);
            expect(strlen($output))->toBeGreaterThan(1000);
        });
    });
});
