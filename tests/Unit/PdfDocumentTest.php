<?php

declare(strict_types=1);

use PdfParse\FlatPdf\PdfDocument;

describe('PdfDocument', function () {

    describe('object allocation', function () {
        it('allocates sequential IDs starting from 3', function () {
            $doc = new PdfDocument();
            expect($doc->allocateObject())->toBe(3);
            expect($doc->allocateObject())->toBe(4);
            expect($doc->allocateObject())->toBe(5);
        });
    });

    describe('getPagesObjId()', function () {
        it('returns 2', function () {
            expect((new PdfDocument())->getPagesObjId())->toBe(2);
        });
    });

    describe('output()', function () {
        it('starts with PDF header', function () {
            $output = (new PdfDocument())->output();
            expect(str_starts_with($output, '%PDF-1.4'))->toBeTrue();
        });

        it('contains binary header marker', function () {
            $output = (new PdfDocument())->output();
            expect(str_contains($output, "\xE2\xE3\xCF\xD3"))->toBeTrue();
        });

        it('ends with %%EOF', function () {
            $output = (new PdfDocument())->output();
            expect(str_contains($output, '%%EOF'))->toBeTrue();
        });

        it('contains xref table', function () {
            $output = (new PdfDocument())->output();
            expect(str_contains($output, 'xref'))->toBeTrue();
            expect(str_contains($output, 'startxref'))->toBeTrue();
        });

        it('contains trailer with Root reference', function () {
            $output = (new PdfDocument())->output();
            expect(str_contains($output, 'trailer'))->toBeTrue();
            expect(str_contains($output, '/Root 1 0 R'))->toBeTrue();
        });

        it('contains catalog object', function () {
            $output = (new PdfDocument())->output();
            expect(str_contains($output, '/Type /Catalog'))->toBeTrue();
        });

        it('contains pages object with zero count when empty', function () {
            $output = (new PdfDocument())->output();
            expect(str_contains($output, '/Type /Pages'))->toBeTrue();
            expect(str_contains($output, '/Count 0'))->toBeTrue();
        });

        it('includes page references when pages are added', function () {
            $doc = new PdfDocument();
            $pageId = $doc->allocateObject();
            $doc->setObject($pageId, '<< /Type /Page >>');
            $doc->addPage($pageId);
            $output = $doc->output();
            expect(str_contains($output, '/Count 1'))->toBeTrue();
            expect(str_contains($output, "{$pageId} 0 R"))->toBeTrue();
        });

        it('includes custom object content', function () {
            $doc = new PdfDocument();
            $id = $doc->allocateObject();
            $doc->setObject($id, '<< /Type /Font /BaseFont /Helvetica >>');
            $output = $doc->output();
            expect(str_contains($output, '/Type /Font /BaseFont /Helvetica'))->toBeTrue();
        });

        it('handles multiple pages', function () {
            $doc = new PdfDocument();
            $p1 = $doc->allocateObject();
            $p2 = $doc->allocateObject();
            $doc->addPage($p1);
            $doc->addPage($p2);
            $output = $doc->output();
            expect(str_contains($output, '/Count 2'))->toBeTrue();
        });

        it('uses empty dict for unset objects', function () {
            $doc = new PdfDocument();
            $id = $doc->allocateObject();
            $output = $doc->output();
            expect(str_contains($output, "{$id} 0 obj\n<< >>\nendobj"))->toBeTrue();
        });
    });
});
