<?php

declare(strict_types=1);

namespace PdfParse\FlatPdf;

/**
 * UTF-8 to Windows-1252 encoding conversion for PDF content streams.
 *
 * Type1 fonts with /WinAnsiEncoding can only display characters in the
 * Windows-1252 codepage (0x00-0xFF). This class converts UTF-8 text to
 * Windows-1252, with transliteration for close approximations and '?'
 * fallback for unmappable characters.
 */
class Encoding
{
    /**
     * Convert a UTF-8 string to Windows-1252.
     *
     * Characters with a direct mapping are converted exactly.
     * Characters with a close transliteration are approximated.
     * Characters with no mapping at all become '?'.
     *
     * Safe to call on pure-ASCII text (returns immediately).
     */
    public static function toWin1252(string $text): string
    {
        if ($text === '') {
            return '';
        }

        // Fast path: pure ASCII has no bytes >= 0x80, valid in both encodings.
        if (!preg_match('/[\x80-\xFF]/', $text)) {
            return $text;
        }

        // If the text has high bytes but is NOT valid UTF-8, it is already
        // Windows-1252 (e.g. from a previous conversion). Return as-is.
        if (!mb_check_encoding($text, 'UTF-8')) {
            return $text;
        }

        // Primary: iconv with //TRANSLIT for best approximations.
        $result = @iconv('UTF-8', 'CP1252//TRANSLIT', $text);

        if ($result !== false) {
            return $result;
        }

        // Fallback: convert character-by-character when the full string fails
        // (contains a mix of convertible and unconvertible characters).
        return self::convertCharByChar($text);
    }

    private static function convertCharByChar(string $text): string
    {
        $result = '';
        $length = mb_strlen($text, 'UTF-8');

        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($text, $i, 1, 'UTF-8');
            $converted = @iconv('UTF-8', 'CP1252//TRANSLIT', $char);
            $result .= $converted !== false ? $converted : '?';
        }

        return $result;
    }
}
