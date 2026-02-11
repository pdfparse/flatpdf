<?php

declare(strict_types=1);

namespace PdfParse\FlatPdf;

/**
 * Styling configuration for the PDF writer.
 * All measurements are in PDF points (1 point = 1/72 inch).
 */
class Style
{
    /**
     * @param list<float> $textColor
     * @param list<float> $headingColor
     * @param list<float> $tableHeaderBg
     * @param list<float> $tableHeaderColor
     * @param list<float> $tableRowBg
     * @param list<float> $tableAltRowBg
     * @param list<float> $tableBorderColor
     * @param list<float> $headerFooterColor
     */
    public function __construct(
        // Page
        public float $pageWidth = 612,      // Letter = 612, A4 = 595.28
        public float $pageHeight = 792,     // Letter = 792, A4 = 841.89
        public float $marginTop = 60,
        public float $marginBottom = 60,
        public float $marginLeft = 50,
        public float $marginRight = 50,

        // Body text
        public string $fontFamily = 'Helvetica',
        public float $fontSize = 9,
        public float $lineHeight = 1.4,
        public array $textColor = [0.2, 0.2, 0.2],

        // Headings
        public float $h1Size = 20,
        public float $h2Size = 15,
        public float $h3Size = 12,
        public float $headingSpaceBefore = 16,
        public float $headingSpaceAfter = 6,
        public array $headingColor = [0.1, 0.1, 0.1],

        // Tables
        public float $tableFontSize = 8,
        public float $tableCellPadding = 5,
        public float $tableLineWidth = 0.5,
        public array $tableHeaderBg = [0.22, 0.40, 0.65],
        public array $tableHeaderColor = [1.0, 1.0, 1.0],
        public string $tableHeaderFont = 'Helvetica-Bold',
        public array $tableRowBg = [1.0, 1.0, 1.0],
        public array $tableAltRowBg = [0.95, 0.96, 0.98],
        public array $tableBorderColor = [0.78, 0.80, 0.83],
        public bool $tableStriped = true,
        public bool $tableRepeatHeaderOnNewPage = true,

        // Header / Footer
        public bool $showPageNumbers = true,
        public string $pageNumberFormat = 'Page {page} of {pages}',
        public float $headerFooterFontSize = 7,
        public array $headerFooterColor = [0.5, 0.5, 0.5],
        public string $headerText = '',
        public string $footerText = '',

        // Paragraph spacing
        public float $paragraphSpacing = 8,

        // Compression
        public bool $compress = true,
    ) {
    }

    /** Dense layout for maximum data per page. */
    public static function compact(): self
    {
        return new self(
            fontSize: 8,
            lineHeight: 1.3,
            marginTop: 45,
            marginBottom: 45,
            marginLeft: 36,
            marginRight: 36,
            tableFontSize: 7,
            tableCellPadding: 3,
            paragraphSpacing: 4,
            h1Size: 16,
            h2Size: 12,
            h3Size: 10,
        );
    }

    /** A4 paper (210mm x 297mm). */
    public static function a4(): self
    {
        return new self(
            pageWidth: 595.28,
            pageHeight: 841.89,
        );
    }

    /** Landscape letter — great for wide tables. */
    public static function landscape(): self
    {
        return new self(
            pageWidth: 792,
            pageHeight: 612,
        );
    }

    /** Landscape + compact — maximum columns. */
    public static function landscapeCompact(): self
    {
        return new self(
            pageWidth: 792,
            pageHeight: 612,
            fontSize: 8,
            lineHeight: 1.3,
            marginTop: 40,
            marginBottom: 40,
            marginLeft: 36,
            marginRight: 36,
            tableFontSize: 7,
            tableCellPadding: 3,
            paragraphSpacing: 4,
        );
    }

    public function contentWidth(): float
    {
        return $this->pageWidth - $this->marginLeft - $this->marginRight;
    }

    public function contentHeight(): float
    {
        return $this->pageHeight - $this->marginTop - $this->marginBottom;
    }

    public function topY(): float
    {
        return $this->pageHeight - $this->marginTop;
    }

    public function bottomY(): float
    {
        return $this->marginBottom;
    }
}
