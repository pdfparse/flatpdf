<?php

declare(strict_types=1);

use PdfParse\FlatPdf\Style;

describe('Style', function () {

    describe('default constructor', function () {
        it('uses Letter page dimensions', function () {
            $style = new Style();
            expect($style->pageWidth)->toBe(612.0);
            expect($style->pageHeight)->toBe(792.0);
        });

        it('has default margins', function () {
            $style = new Style();
            expect($style->marginTop)->toBe(60.0);
            expect($style->marginBottom)->toBe(60.0);
            expect($style->marginLeft)->toBe(50.0);
            expect($style->marginRight)->toBe(50.0);
        });

        it('uses Helvetica font family', function () {
            expect((new Style())->fontFamily)->toBe('Helvetica');
        });

        it('has default font size of 9', function () {
            expect((new Style())->fontSize)->toBe(9.0);
        });

        it('has default line height of 1.4', function () {
            expect((new Style())->lineHeight)->toBe(1.4);
        });

        it('enables compression by default', function () {
            expect((new Style())->compress)->toBeTrue();
        });

        it('enables page numbers by default', function () {
            expect((new Style())->showPageNumbers)->toBeTrue();
        });

        it('enables table striping by default', function () {
            expect((new Style())->tableStriped)->toBeTrue();
        });

        it('enables table header repeat by default', function () {
            expect((new Style())->tableRepeatHeaderOnNewPage)->toBeTrue();
        });

        it('has empty header and footer text', function () {
            $style = new Style();
            expect($style->headerText)->toBe('');
            expect($style->footerText)->toBe('');
        });

        it('uses default heading sizes', function () {
            $style = new Style();
            expect($style->h1Size)->toBe(20.0);
            expect($style->h2Size)->toBe(15.0);
            expect($style->h3Size)->toBe(12.0);
        });

        it('uses Helvetica-Bold for table headers', function () {
            expect((new Style())->tableHeaderFont)->toBe('Helvetica-Bold');
        });

        it('has default page number format', function () {
            expect((new Style())->pageNumberFormat)->toBe('Page {page} of {pages}');
        });
    });

    describe('constructor with overrides', function () {
        it('accepts custom page dimensions', function () {
            $style = new Style(pageWidth: 500, pageHeight: 700);
            expect($style->pageWidth)->toBe(500.0);
            expect($style->pageHeight)->toBe(700.0);
        });

        it('accepts custom margins', function () {
            $style = new Style(marginTop: 100, marginBottom: 80, marginLeft: 30, marginRight: 40);
            expect($style->marginTop)->toBe(100.0);
            expect($style->marginBottom)->toBe(80.0);
            expect($style->marginLeft)->toBe(30.0);
            expect($style->marginRight)->toBe(40.0);
        });

        it('accepts custom text color', function () {
            $style = new Style(textColor: [1.0, 0.0, 0.0]);
            expect($style->textColor)->toBe([1.0, 0.0, 0.0]);
        });

        it('can disable compression', function () {
            expect((new Style(compress: false))->compress)->toBeFalse();
        });

        it('can disable page numbers', function () {
            expect((new Style(showPageNumbers: false))->showPageNumbers)->toBeFalse();
        });

        it('can set header and footer text', function () {
            $style = new Style(headerText: 'Report', footerText: 'Confidential');
            expect($style->headerText)->toBe('Report');
            expect($style->footerText)->toBe('Confidential');
        });

        it('accepts custom font family', function () {
            expect((new Style(fontFamily: 'Courier'))->fontFamily)->toBe('Courier');
        });
    });

    describe('make()', function () {
        it('creates a default style with no arguments', function () {
            $style = Style::make();
            expect($style->pageWidth)->toBe(612.0);
            expect($style->pageHeight)->toBe(792.0);
            expect($style->fontSize)->toBe(9.0);
        });

        it('accepts named arguments like the constructor', function () {
            $style = Style::make(fontSize: 14, headerText: 'Test', compress: false);
            expect($style->fontSize)->toBe(14.0);
            expect($style->headerText)->toBe('Test');
            expect($style->compress)->toBeFalse();
        });

        it('returns the same result as the constructor', function () {
            $fromNew = new Style(pageWidth: 500, marginTop: 80);
            $fromMake = Style::make(pageWidth: 500, marginTop: 80);
            expect($fromMake->pageWidth)->toBe($fromNew->pageWidth);
            expect($fromMake->marginTop)->toBe($fromNew->marginTop);
        });
    });

    describe('factory methods', function () {
        describe('compact()', function () {
            it('uses smaller font sizes', function () {
                $style = Style::compact();
                expect($style->fontSize)->toBe(8.0);
                expect($style->tableFontSize)->toBe(7.0);
            });

            it('uses tighter margins', function () {
                $style = Style::compact();
                expect($style->marginTop)->toBe(45.0);
                expect($style->marginBottom)->toBe(45.0);
                expect($style->marginLeft)->toBe(36.0);
                expect($style->marginRight)->toBe(36.0);
            });

            it('uses smaller paragraph spacing', function () {
                expect(Style::compact()->paragraphSpacing)->toBe(4.0);
            });

            it('uses Letter page dimensions', function () {
                $style = Style::compact();
                expect($style->pageWidth)->toBe(612.0);
                expect($style->pageHeight)->toBe(792.0);
            });

            it('uses smaller heading sizes', function () {
                $style = Style::compact();
                expect($style->h1Size)->toBe(16.0);
                expect($style->h2Size)->toBe(12.0);
                expect($style->h3Size)->toBe(10.0);
            });
        });

        describe('a4()', function () {
            it('uses A4 page dimensions', function () {
                $style = Style::a4();
                expect($style->pageWidth)->toBe(595.28);
                expect($style->pageHeight)->toBe(841.89);
            });

            it('uses default margins', function () {
                $style = Style::a4();
                expect($style->marginTop)->toBe(60.0);
                expect($style->marginLeft)->toBe(50.0);
            });
        });

        describe('landscape()', function () {
            it('swaps width and height', function () {
                $style = Style::landscape();
                expect($style->pageWidth)->toBe(792.0);
                expect($style->pageHeight)->toBe(612.0);
            });
        });

        describe('landscapeCompact()', function () {
            it('uses landscape dimensions with compact settings', function () {
                $style = Style::landscapeCompact();
                expect($style->pageWidth)->toBe(792.0);
                expect($style->pageHeight)->toBe(612.0);
                expect($style->fontSize)->toBe(8.0);
                expect($style->tableFontSize)->toBe(7.0);
            });
        });
    });

    describe('computed properties', function () {
        it('calculates content width as pageWidth minus margins', function () {
            $style = new Style();
            expect($style->contentWidth())->toBe(512.0);
        });

        it('calculates content height as pageHeight minus margins', function () {
            $style = new Style();
            expect($style->contentHeight())->toBe(672.0);
        });

        it('calculates topY as pageHeight minus marginTop', function () {
            $style = new Style();
            expect($style->topY())->toBe(732.0);
        });

        it('calculates bottomY as marginBottom', function () {
            $style = new Style();
            expect($style->bottomY())->toBe(60.0);
        });

        it('computes correctly with custom margins', function () {
            $style = new Style(marginTop: 100, marginBottom: 80, marginLeft: 30, marginRight: 40);
            expect($style->contentWidth())->toBe(542.0);
            expect($style->contentHeight())->toBe(612.0);
            expect($style->topY())->toBe(692.0);
            expect($style->bottomY())->toBe(80.0);
        });

        it('computes correctly for A4', function () {
            $style = Style::a4();
            expect($style->contentWidth())->toBe(595.28 - 50 - 50);
            expect($style->contentHeight())->toBe(841.89 - 60 - 60);
        });
    });
});
