<?php

declare(strict_types=1);

namespace PdfParse\FlatPdf;

/**
 * Character width metrics for the 14 standard PDF fonts.
 * Widths are in units of 1/1000 of the font size.
 */
class FontMetrics
{
    private const FONT_WIDTHS = [
        'Helvetica' => [
            'default' => 556,
            ' ' => 278, '!' => 278, '"' => 355, '#' => 556, '$' => 556, '%' => 889,
            '&' => 667, '\'' => 191, '(' => 333, ')' => 333, '*' => 389, '+' => 584,
            ',' => 278, '-' => 333, '.' => 278, '/' => 278,
            '0' => 556, '1' => 556, '2' => 556, '3' => 556, '4' => 556,
            '5' => 556, '6' => 556, '7' => 556, '8' => 556, '9' => 556,
            ':' => 278, ';' => 278, '<' => 584, '=' => 584, '>' => 584, '?' => 556,
            '@' => 1015,
            'A' => 667, 'B' => 667, 'C' => 722, 'D' => 722, 'E' => 667, 'F' => 611,
            'G' => 778, 'H' => 722, 'I' => 278, 'J' => 500, 'K' => 667, 'L' => 556,
            'M' => 833, 'N' => 722, 'O' => 778, 'P' => 667, 'Q' => 778, 'R' => 722,
            'S' => 667, 'T' => 611, 'U' => 722, 'V' => 667, 'W' => 944, 'X' => 667,
            'Y' => 667, 'Z' => 611,
            '[' => 278, '\\' => 278, ']' => 278, '^' => 469, '_' => 556, '`' => 333,
            'a' => 556, 'b' => 556, 'c' => 500, 'd' => 556, 'e' => 556, 'f' => 278,
            'g' => 556, 'h' => 556, 'i' => 222, 'j' => 222, 'k' => 500, 'l' => 222,
            'm' => 833, 'n' => 556, 'o' => 556, 'p' => 556, 'q' => 556, 'r' => 333,
            's' => 500, 't' => 278, 'u' => 556, 'v' => 500, 'w' => 722, 'x' => 500,
            'y' => 500, 'z' => 500,
            '{' => 334, '|' => 260, '}' => 334, '~' => 584,
        ],
        'Helvetica-Bold' => [
            'default' => 556,
            ' ' => 278, '!' => 333, '"' => 474, '#' => 556, '$' => 556, '%' => 889,
            '&' => 722, '\'' => 238, '(' => 333, ')' => 333, '*' => 389, '+' => 584,
            ',' => 278, '-' => 333, '.' => 278, '/' => 278,
            '0' => 556, '1' => 556, '2' => 556, '3' => 556, '4' => 556,
            '5' => 556, '6' => 556, '7' => 556, '8' => 556, '9' => 556,
            ':' => 333, ';' => 333, '<' => 584, '=' => 584, '>' => 584, '?' => 611,
            '@' => 975,
            'A' => 722, 'B' => 722, 'C' => 722, 'D' => 722, 'E' => 667, 'F' => 611,
            'G' => 778, 'H' => 722, 'I' => 278, 'J' => 556, 'K' => 722, 'L' => 611,
            'M' => 833, 'N' => 722, 'O' => 778, 'P' => 667, 'Q' => 778, 'R' => 722,
            'S' => 667, 'T' => 611, 'U' => 722, 'V' => 667, 'W' => 944, 'X' => 667,
            'Y' => 667, 'Z' => 611,
            '[' => 333, '\\' => 278, ']' => 333, '^' => 584, '_' => 556, '`' => 333,
            'a' => 556, 'b' => 611, 'c' => 556, 'd' => 611, 'e' => 556, 'f' => 333,
            'g' => 611, 'h' => 611, 'i' => 278, 'j' => 278, 'k' => 556, 'l' => 278,
            'm' => 889, 'n' => 611, 'o' => 611, 'p' => 611, 'q' => 611, 'r' => 389,
            's' => 556, 't' => 333, 'u' => 611, 'v' => 556, 'w' => 778, 'x' => 556,
            'y' => 556, 'z' => 500,
            '{' => 389, '|' => 280, '}' => 389, '~' => 584,
        ],
        'Helvetica-Oblique' => [
            'default' => 556,
        ],
        'Helvetica-BoldOblique' => [
            'default' => 556,
        ],
        'Courier' => [
            'default' => 600,
        ],
        'Courier-Bold' => [
            'default' => 600,
        ],
        'Courier-Oblique' => [
            'default' => 600,
        ],
        'Courier-BoldOblique' => [
            'default' => 600,
        ],
        'Times-Roman' => [
            'default' => 500,
            ' ' => 250, '!' => 333, '"' => 408, '#' => 500, '$' => 500, '%' => 833,
            '&' => 778, '\'' => 180, '(' => 333, ')' => 333, '*' => 500, '+' => 564,
            ',' => 250, '-' => 333, '.' => 250, '/' => 278,
            '0' => 500, '1' => 500, '2' => 500, '3' => 500, '4' => 500,
            '5' => 500, '6' => 500, '7' => 500, '8' => 500, '9' => 500,
            ':' => 278, ';' => 278,
            'A' => 722, 'B' => 667, 'C' => 667, 'D' => 722, 'E' => 611, 'F' => 556,
            'G' => 722, 'H' => 722, 'I' => 333, 'J' => 389, 'K' => 722, 'L' => 611,
            'M' => 889, 'N' => 722, 'O' => 722, 'P' => 556, 'Q' => 722, 'R' => 667,
            'S' => 556, 'T' => 611, 'U' => 722, 'V' => 722, 'W' => 944, 'X' => 722,
            'Y' => 722, 'Z' => 611,
            'a' => 444, 'b' => 500, 'c' => 444, 'd' => 500, 'e' => 444, 'f' => 333,
            'g' => 500, 'h' => 500, 'i' => 278, 'j' => 278, 'k' => 500, 'l' => 278,
            'm' => 778, 'n' => 500, 'o' => 500, 'p' => 500, 'q' => 500, 'r' => 333,
            's' => 389, 't' => 278, 'u' => 500, 'v' => 500, 'w' => 722, 'x' => 500,
            'y' => 500, 'z' => 444,
        ],
        'Times-Bold' => [
            'default' => 500,
            ' ' => 250,
            'A' => 722, 'B' => 667, 'C' => 722, 'D' => 722, 'E' => 667, 'F' => 611,
            'G' => 778, 'H' => 778, 'I' => 389, 'J' => 500, 'K' => 778, 'L' => 667,
            'M' => 944, 'N' => 722, 'O' => 778, 'P' => 611, 'Q' => 778, 'R' => 722,
            'S' => 556, 'T' => 667, 'U' => 722, 'V' => 722, 'W' => 1000, 'X' => 722,
            'Y' => 722, 'Z' => 667,
            'a' => 500, 'b' => 556, 'c' => 444, 'd' => 556, 'e' => 444, 'f' => 333,
            'g' => 500, 'h' => 556, 'i' => 278, 'j' => 333, 'k' => 556, 'l' => 278,
            'm' => 833, 'n' => 556, 'o' => 500, 'p' => 556, 'q' => 556, 'r' => 444,
            's' => 389, 't' => 333, 'u' => 556, 'v' => 500, 'w' => 722, 'x' => 500,
            'y' => 500, 'z' => 444,
        ],
        'Times-Italic' => [
            'default' => 500,
            ' ' => 250,
        ],
        'Times-BoldItalic' => [
            'default' => 500,
            ' ' => 250,
        ],
    ];

    private const FONT_ALIASES = [
        'helvetica' => 'Helvetica',
        'arial' => 'Helvetica',
        'sans-serif' => 'Helvetica',
        'courier' => 'Courier',
        'monospace' => 'Courier',
        'times' => 'Times-Roman',
        'times-roman' => 'Times-Roman',
        'serif' => 'Times-Roman',
    ];

    public static function resolveFontName(string $family, bool $bold = false, bool $italic = false): string
    {
        $family = self::FONT_ALIASES[strtolower($family)] ?? $family;

        if ($family === 'Helvetica') {
            if ($bold && $italic) return 'Helvetica-BoldOblique';
            if ($bold) return 'Helvetica-Bold';
            if ($italic) return 'Helvetica-Oblique';
            return 'Helvetica';
        }
        if ($family === 'Courier') {
            if ($bold && $italic) return 'Courier-BoldOblique';
            if ($bold) return 'Courier-Bold';
            if ($italic) return 'Courier-Oblique';
            return 'Courier';
        }
        if ($family === 'Times-Roman') {
            if ($bold && $italic) return 'Times-BoldItalic';
            if ($bold) return 'Times-Bold';
            if ($italic) return 'Times-Italic';
            return 'Times-Roman';
        }

        return $family;
    }

    public static function charWidth(string $char, string $fontName): int
    {
        $lookupFont = match ($fontName) {
            'Helvetica-Oblique' => 'Helvetica',
            'Helvetica-BoldOblique' => 'Helvetica-Bold',
            'Courier-Bold', 'Courier-Oblique', 'Courier-BoldOblique' => 'Courier',
            'Times-Italic' => 'Times-Roman',
            'Times-BoldItalic' => 'Times-Bold',
            default => $fontName,
        };

        $widths = self::FONT_WIDTHS[$lookupFont] ?? self::FONT_WIDTHS['Helvetica'];
        return $widths[$char] ?? $widths['default'];
    }

    public static function stringWidth(string $text, string $fontName, float $fontSize): float
    {
        $width = 0;
        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $width += self::charWidth($text[$i], $fontName);
        }
        return ($width / 1000.0) * $fontSize;
    }

    /** @return list<string> */
    public static function wordWrap(string $text, string $fontName, float $fontSize, float $maxWidth): array
    {
        if ($maxWidth <= 0) {
            return [$text];
        }

        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            if ($currentLine === '') {
                $testLine = $word;
            } else {
                $testLine = $currentLine . ' ' . $word;
            }

            $testWidth = self::stringWidth($testLine, $fontName, $fontSize);

            if ($testWidth > $maxWidth && $currentLine !== '') {
                $lines[] = $currentLine;
                $currentLine = $word;
            } else {
                $currentLine = $testLine;
            }
        }

        if ($currentLine !== '') {
            $lines[] = $currentLine;
        }

        return $lines ?: [''];
    }
}
