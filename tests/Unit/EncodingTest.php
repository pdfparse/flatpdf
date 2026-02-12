<?php

declare(strict_types=1);

use PdfParse\FlatPdf\Encoding;

describe('Encoding', function () {

    describe('toWin1252()', function () {

        it('passes through pure ASCII text unchanged', function () {
            expect(Encoding::toWin1252('Hello World 123'))->toBe('Hello World 123');
        });

        it('passes through empty string', function () {
            expect(Encoding::toWin1252(''))->toBe('');
        });

        it('converts UTF-8 em dash to Windows-1252', function () {
            $result = Encoding::toWin1252("\xE2\x80\x94");
            expect($result)->toBe("\x97");
        });

        it('converts UTF-8 en dash to Windows-1252', function () {
            $result = Encoding::toWin1252("\xE2\x80\x93");
            expect($result)->toBe("\x96");
        });

        it('converts UTF-8 Euro sign to Windows-1252', function () {
            $result = Encoding::toWin1252("\xE2\x82\xAC");
            expect($result)->toBe("\x80");
        });

        it('converts smart quotes to Windows-1252', function () {
            $input = "\xE2\x80\x98test\xE2\x80\x99";
            $result = Encoding::toWin1252($input);
            expect($result)->toBe("\x91test\x92");
        });

        it('converts superscript and degree characters', function () {
            $input = "100\xC2\xB2 at 45\xC2\xB0";
            $result = Encoding::toWin1252($input);
            expect($result)->toBe("100\xB2 at 45\xB0");
        });

        it('converts accented characters', function () {
            $result = Encoding::toWin1252("caf\xC3\xA9");
            expect($result)->toBe("caf\xE9");
        });

        it('converts pound and yen signs', function () {
            $result = Encoding::toWin1252("\xC2\xA3" . "500");
            expect($result)->toBe("\xA3" . "500");

            $result = Encoding::toWin1252("\xC2\xA5" . "500");
            expect($result)->toBe("\xA5" . "500");
        });

        it('replaces unmappable characters with fallback', function () {
            $input = "Price: \xE2\x82\xB9" . "500";
            $result = @Encoding::toWin1252($input);
            expect(str_contains($result, '500'))->toBeTrue();
            expect(str_contains($result, "\xE2"))->toBeFalse();
        });

        it('handles mixed ASCII and UTF-8 text', function () {
            $input = "Total: \xE2\x82\xAC" . "1,000 \xE2\x80\x94 paid";
            $result = Encoding::toWin1252($input);
            expect($result)->toBe("Total: \x80" . "1,000 \x97 paid");
        });

        it('handles string with only unmappable characters', function () {
            $input = "\xF0\x9F\x98\x80";
            $result = Encoding::toWin1252($input);
            expect(str_contains($result, "\xF0"))->toBeFalse();
        });

        it('converts copyright, registered, and trademark symbols', function () {
            $result = Encoding::toWin1252("\xC2\xA9 2024");
            expect($result)->toBe("\xA9 2024");

            $result = Encoding::toWin1252("Brand\xC2\xAE");
            expect($result)->toBe("Brand\xAE");

            $result = Encoding::toWin1252("Name\xE2\x84\xA2");
            expect($result)->toBe("Name\x99");
        });
    });
});
