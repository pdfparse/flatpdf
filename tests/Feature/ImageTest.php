<?php

declare(strict_types=1);

use PdfParse\FlatPdf\FlatPdf;
use PdfParse\FlatPdf\Style;

describe('Images', function () {

    beforeEach(function () {
        $this->style = new Style(compress: false);
    });

    describe('imageFromString()', function () {
        it('embeds a JPEG image into the PDF', function () {
            $pdf = new FlatPdf($this->style);
            $jpeg = createTestJpeg(100, 60);
            $pdf->imageFromString($jpeg, width: 100);
            $output = $pdf->output();
            expect(str_contains($output, '/Subtype /Image'))->toBeTrue();
            expect(str_contains($output, '/Filter /DCTDecode'))->toBeTrue();
        });

        it('deduplicates identical images by content hash', function () {
            $pdf = new FlatPdf($this->style);
            $jpeg = createTestJpeg(50, 50);
            $pdf->imageFromString($jpeg, width: 50);
            $pdf->imageFromString($jpeg, width: 80);
            $output = $pdf->output();
            $count = substr_count($output, '/Subtype /Image');
            expect($count)->toBe(1);
        });

        it('creates separate entries for different images', function () {
            $pdf = new FlatPdf($this->style);
            $jpeg1 = createTestJpeg(50, 50);
            $jpeg2 = createTestJpeg(80, 40);
            $pdf->imageFromString($jpeg1, width: 50);
            $pdf->imageFromString($jpeg2, width: 80);
            $output = $pdf->output();
            $count = substr_count($output, '/Subtype /Image');
            expect($count)->toBe(2);
        });

        it('supports center alignment', function () {
            $pdf = new FlatPdf($this->style);
            $jpeg = createTestJpeg(50, 50);
            $pdf->imageFromString($jpeg, width: 50, options: ['align' => 'center']);
            $output = $pdf->output();
            expect(str_contains($output, '/Im1 Do'))->toBeTrue();
        });

        it('supports right alignment', function () {
            $pdf = new FlatPdf($this->style);
            $jpeg = createTestJpeg(50, 50);
            $pdf->imageFromString($jpeg, width: 50, options: ['align' => 'right']);
            $output = $pdf->output();
            expect(str_contains($output, '/Im1 Do'))->toBeTrue();
        });

        it('triggers page break when image does not fit', function () {
            $pdf = new FlatPdf($this->style);
            // Fill up the page with text so the image won't fit
            while ($pdf->getRemainingSpace() > 30) {
                $pdf->text('Filling up the page with text content.');
            }
            $jpeg = createTestJpeg(100, 100);
            $pdf->imageFromString($jpeg, width: 100, height: 100);
            expect($pdf->getCurrentPage())->toBeGreaterThan(1);
        });

        it('throws on invalid JPEG data', function () {
            $pdf = new FlatPdf($this->style);
            expect(fn() => $pdf->imageFromString('not a jpeg', width: 100))
                ->toThrow(RuntimeException::class);
        });

        it('throws on truncated JPEG data', function () {
            $pdf = new FlatPdf($this->style);
            expect(fn() => $pdf->imageFromString("\xFF\xD8\xFF", width: 100))
                ->toThrow(RuntimeException::class);
        });
    });

    describe('image()', function () {
        it('reads a JPEG file from disk and embeds it', function () {
            $tmpFile = tempnam(sys_get_temp_dir(), 'flatpdf_test_') . '.jpg';
            $jpeg = createTestJpeg(80, 60);
            file_put_contents($tmpFile, $jpeg);

            $pdf = new FlatPdf($this->style);
            $pdf->image($tmpFile, width: 100);
            $output = $pdf->output();
            expect(str_contains($output, '/Filter /DCTDecode'))->toBeTrue();

            unlink($tmpFile);
        });

        it('throws on non-existent file', function () {
            $pdf = new FlatPdf($this->style);
            expect(fn() => $pdf->image('/nonexistent/path.jpg'))
                ->toThrow(RuntimeException::class);
        });
    });

    describe('imageFromDisk()', function () {
        it('reads from a disk-like object with get() method', function () {
            $jpeg = createTestJpeg(40, 40);
            $mockDisk = new class($jpeg) {
                public function __construct(private string $data) {}
                public function get(string $path): ?string { return $this->data; }
            };

            $pdf = new FlatPdf($this->style);
            $pdf->imageFromDisk($mockDisk, 'test.jpg', width: 50);
            $output = $pdf->output();
            expect(str_contains($output, '/Filter /DCTDecode'))->toBeTrue();
        });

        it('throws when disk object lacks get() method', function () {
            $pdf = new FlatPdf($this->style);
            $badDisk = new stdClass();
            expect(fn() => $pdf->imageFromDisk($badDisk, 'test.jpg'))
                ->toThrow(InvalidArgumentException::class);
        });

        it('throws when disk returns null', function () {
            $mockDisk = new class {
                public function get(string $path): ?string { return null; }
            };
            $pdf = new FlatPdf($this->style);
            expect(fn() => $pdf->imageFromDisk($mockDisk, 'missing.jpg'))
                ->toThrow(RuntimeException::class);
        });
    });
});
