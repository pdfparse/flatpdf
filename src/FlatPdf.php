<?php

declare(strict_types=1);

namespace PdfParse\FlatPdf;

/**
 * FlatPdf — Zero-dependency PDF writer optimized for long documents with tables.
 *
 * Features:
 * - Automatic page breaks with configurable headers/footers
 * - Tables with auto-sizing columns, word-wrap, striped rows, repeated headers
 * - Text with word-wrap, bold/italic, headings
 * - Horizontal rules, spacing, page breaks
 * - Low memory: writes page-by-page
 *
 * Usage:
 *   $pdf = new FlatPdf(Style::compact());
 *   $pdf->h1('Report Title');
 *   $pdf->dataTable($records);
 *   $pdf->save('report.pdf');
 */
class FlatPdf
{
    private PdfDocument $doc;
    private Style $style;

    // Font tracking
    /** @var array<string, int> */
    private array $fonts = [];
    /** @var array<string, string> */
    private array $fontKeys = [];
    private int $fontCounter = 0;

    // Page state
    private int $currentPageObjId = 0;
    private int $currentContentObjId = 0;
    private string $currentPageStream = '';
    private float $cursorY;
    private float $cursorX;
    private int $pageCount = 0;

    // State tracking
    private string $activeFont = '';
    private float $activeFontSize = 0;
    /** @var list<float> */
    private array $activeColor = [0, 0, 0];
    private bool $documentFinished = false;
    /** @var array<int, string> Raw page streams keyed by content object ID, compressed in output(). */
    private array $pageStreams = [];

    // Image tracking
    /** @var array<string, array{objId: int, width: int, height: int, name: string}> */
    private array $images = [];
    private int $imageCounter = 0;
    /** @var list<string> */
    private array $pageImages = [];

    public function __construct(?Style $style = null)
    {
        $this->style = $style ?? new Style();
        $this->doc = new PdfDocument();

        // Register the standard fonts we'll use
        foreach ([
            'Helvetica', 'Helvetica-Bold', 'Helvetica-Oblique', 'Helvetica-BoldOblique',
            'Courier', 'Courier-Bold',
            'Times-Roman', 'Times-Bold',
        ] as $font) {
            $this->registerFont($font);
        }

        $this->newPage();
    }

    // ─── Font Management ──────────────────────────────────────────────

    private function registerFont(string $fontName): void
    {
        if (isset($this->fonts[$fontName])) return;

        $objId = $this->doc->allocateObject();
        $this->doc->setObject($objId, "<< /Type /Font /Subtype /Type1 /BaseFont /{$fontName} /Encoding /WinAnsiEncoding >>");
        $this->fontCounter++;
        $this->fonts[$fontName] = $objId;
        $this->fontKeys[$fontName] = "/F{$this->fontCounter}";
    }

    private function fontResourceDict(): string
    {
        $entries = [];
        foreach ($this->fonts as $name => $objId) {
            $entries[] = "{$this->fontKeys[$name]} {$objId} 0 R";
        }
        return '<< ' . implode(' ', $entries) . ' >>';
    }

    // ─── Page Management ──────────────────────────────────────────────

    public function newPage(): void
    {
        if ($this->currentPageObjId > 0) {
            $this->finalizePage();
        }

        $this->pageCount++;
        $this->currentPageObjId = $this->doc->allocateObject();
        $this->currentContentObjId = $this->doc->allocateObject();
        $this->currentPageStream = '';
        $this->cursorY = $this->style->topY();
        $this->cursorX = $this->style->marginLeft;
        $this->activeFont = '';
        $this->activeFontSize = 0;
        $this->activeColor = [-1, -1, -1];
        $this->pageImages = [];

        $this->doc->addPage($this->currentPageObjId);
    }

    private function finalizePage(): void
    {
        $this->renderHeaderFooter();

        // Store raw stream; compression + placeholder replacement happen in output().
        $this->pageStreams[$this->currentContentObjId] = $this->currentPageStream;

        $w = $this->style->pageWidth;
        $h = $this->style->pageHeight;
        $pagesRef = $this->doc->getPagesObjId();
        $contentRef = $this->currentContentObjId;
        $fontDict = $this->fontResourceDict();
        $resourceDict = "/Font {$fontDict}";

        if (!empty($this->pageImages)) {
            $xobjectEntries = [];
            foreach ($this->pageImages as $imgName) {
                foreach ($this->images as $imgData) {
                    if ($imgData['name'] === $imgName) {
                        $xobjectEntries[] = "/{$imgName} {$imgData['objId']} 0 R";
                        break;
                    }
                }
            }
            $resourceDict .= " /XObject << " . implode(' ', $xobjectEntries) . " >>";
        }

        $this->doc->setObject($this->currentPageObjId,
            "<< /Type /Page /Parent {$pagesRef} 0 R " .
            "/MediaBox [0 0 {$w} {$h}] " .
            "/Contents {$contentRef} 0 R " .
            "/Resources << {$resourceDict} >> >>"
        );
    }

    private function renderHeaderFooter(): void
    {
        $s = $this->style;

        if ($s->showPageNumbers) {
            $text = str_replace(
                ['{page}', '{pages}'],
                [(string)$this->pageCount, '___TOTAL_PAGES___'],
                $s->pageNumberFormat
            );
            $this->setFontInStream('Helvetica', $s->headerFooterFontSize);
            $this->setColorInStream($s->headerFooterColor);
            $width = FontMetrics::stringWidth(
                str_replace('___TOTAL_PAGES___', '000', $text),
                'Helvetica', $s->headerFooterFontSize
            );
            $x = $s->pageWidth - $s->marginRight - $width;
            $y = $s->marginBottom - 20;
            $this->currentPageStream .= "BT {$this->fmt($x)} {$this->fmt($y)} Td ({$this->escape($text)}) Tj ET\n";
        }

        if ($s->headerText !== '') {
            $this->setFontInStream('Helvetica', $s->headerFooterFontSize);
            $this->setColorInStream($s->headerFooterColor);
            $y = $s->pageHeight - $s->marginTop + 15;
            $this->currentPageStream .= "BT {$this->fmt($s->marginLeft)} {$this->fmt($y)} Td ({$this->escape($s->headerText)}) Tj ET\n";

            $this->currentPageStream .= "{$this->colorStr($s->headerFooterColor)} RG 0.5 w\n";
            $lineY = $s->pageHeight - $s->marginTop + 8;
            $this->currentPageStream .= "{$this->fmt($s->marginLeft)} {$this->fmt($lineY)} m {$this->fmt($s->pageWidth - $s->marginRight)} {$this->fmt($lineY)} l S\n";
        }

        if ($s->footerText !== '') {
            $this->setFontInStream('Helvetica', $s->headerFooterFontSize);
            $this->setColorInStream($s->headerFooterColor);
            $y = $s->marginBottom - 20;
            $this->currentPageStream .= "BT {$this->fmt($s->marginLeft)} {$this->fmt($y)} Td ({$this->escape($s->footerText)}) Tj ET\n";
        }
    }

    private function ensureSpace(float $requiredHeight): bool
    {
        if ($this->cursorY - $requiredHeight < $this->style->bottomY()) {
            $this->newPage();
            return true;
        }
        return false;
    }

    // ─── Stream Helpers ───────────────────────────────────────────────

    private function setFontInStream(string $fontName, float $size): void
    {
        if ($this->activeFont === $fontName && $this->activeFontSize === $size) return;
        $key = $this->fontKeys[$fontName] ?? $this->fontKeys['Helvetica'];
        $this->currentPageStream .= "BT {$key} {$this->fmt($size)} Tf ET\n";
        $this->activeFont = $fontName;
        $this->activeFontSize = $size;
    }

    /** @param list<float> $rgb */
    private function setColorInStream(array $rgb): void
    {
        if ($this->activeColor === $rgb) return;
        $this->currentPageStream .= "{$this->colorStr($rgb)} rg\n";
        $this->activeColor = $rgb;
    }

    /** @param list<float> $rgb */
    private function colorStr(array $rgb): string
    {
        return "{$this->fmt($rgb[0])} {$this->fmt($rgb[1])} {$this->fmt($rgb[2])}";
    }

    private function fmt(float $val): string
    {
        return rtrim(rtrim(sprintf('%.4f', $val), '0'), '.');
    }

    private function escape(string $text): string
    {
        return str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\\(', '\\)'],
            $text
        );
    }

    // ─── Text Output ──────────────────────────────────────────────────

    /**
     * Write a paragraph of text with automatic word-wrapping.
     *
     * @param list<float>|null $color
     */
    public function text(string $text, ?string $font = null, ?float $size = null, ?array $color = null): void
    {
        $font = $font ?? FontMetrics::resolveFontName($this->style->fontFamily);
        $size = $size ?? $this->style->fontSize;
        $color = $color ?? $this->style->textColor;
        $lineSpacing = $size * $this->style->lineHeight;
        $maxWidth = $this->style->contentWidth();

        $paragraphs = explode("\n", $text);
        foreach ($paragraphs as $pIdx => $paragraph) {
            if (trim($paragraph) === '') {
                $this->ensureSpace($lineSpacing);
                $this->cursorY -= $lineSpacing;
                continue;
            }

            $lines = FontMetrics::wordWrap($paragraph, $font, $size, $maxWidth);

            foreach ($lines as $line) {
                $this->ensureSpace($lineSpacing);
                $this->setFontInStream($font, $size);
                $this->setColorInStream($color);
                $this->currentPageStream .= "BT {$this->fmt($this->cursorX)} {$this->fmt($this->cursorY)} Td ({$this->escape($line)}) Tj ET\n";
                $this->cursorY -= $lineSpacing;
            }

            if ($pIdx < count($paragraphs) - 1) {
                $this->cursorY -= $this->style->paragraphSpacing;
            }
        }
    }

    /** @param list<float>|null $color */
    public function bold(string $text, ?float $size = null, ?array $color = null): void
    {
        $this->text($text, FontMetrics::resolveFontName($this->style->fontFamily, bold: true), $size, $color);
    }

    /** @param list<float>|null $color */
    public function italic(string $text, ?float $size = null, ?array $color = null): void
    {
        $this->text($text, FontMetrics::resolveFontName($this->style->fontFamily, italic: true), $size, $color);
    }

    /** Write monospaced text. */
    public function code(string $text, ?float $size = null): void
    {
        $this->text($text, 'Courier', $size ?? $this->style->fontSize * 0.9);
    }

    // ─── Headings ─────────────────────────────────────────────────────

    public function h1(string $text): void { $this->heading($text, $this->style->h1Size, drawLine: true); }
    public function h2(string $text): void { $this->heading($text, $this->style->h2Size); }
    public function h3(string $text): void { $this->heading($text, $this->style->h3Size); }

    private function heading(string $text, float $size, bool $drawLine = false): void
    {
        $lineSpacing = $size * $this->style->lineHeight;
        $totalHeight = $this->style->headingSpaceBefore + $lineSpacing + $this->style->headingSpaceAfter;

        $this->ensureSpace($totalHeight + 20);
        $this->cursorY -= $this->style->headingSpaceBefore;

        $font = FontMetrics::resolveFontName($this->style->fontFamily, bold: true);
        $this->setFontInStream($font, $size);
        $this->setColorInStream($this->style->headingColor);
        $this->currentPageStream .= "BT {$this->fmt($this->cursorX)} {$this->fmt($this->cursorY)} Td ({$this->escape($text)}) Tj ET\n";
        $this->cursorY -= $lineSpacing;

        if ($drawLine) {
            $lineY = $this->cursorY + 2;
            $this->currentPageStream .= "{$this->colorStr([0.8, 0.8, 0.8])} RG 0.75 w\n";
            $this->currentPageStream .= "{$this->fmt($this->style->marginLeft)} {$this->fmt($lineY)} m ";
            $this->currentPageStream .= "{$this->fmt($this->style->pageWidth - $this->style->marginRight)} {$this->fmt($lineY)} l S\n";
        }

        $this->cursorY -= $this->style->headingSpaceAfter;
    }

    // ─── Visual Elements ──────────────────────────────────────────────

    public function hr(): void
    {
        $this->ensureSpace(12);
        $this->cursorY -= 6;
        $this->currentPageStream .= "{$this->colorStr([0.8, 0.8, 0.8])} RG 0.5 w\n";
        $this->currentPageStream .= "{$this->fmt($this->style->marginLeft)} {$this->fmt($this->cursorY)} m ";
        $this->currentPageStream .= "{$this->fmt($this->style->pageWidth - $this->style->marginRight)} {$this->fmt($this->cursorY)} l S\n";
        $this->cursorY -= 6;
    }

    public function space(float $points = 10): void
    {
        $this->cursorY -= $points;
        if ($this->cursorY < $this->style->bottomY()) {
            $this->newPage();
        }
    }

    public function pageBreak(): void
    {
        $this->newPage();
    }

    // ─── TABLES ───────────────────────────────────────────────────────

    /**
     * Render a table from explicit headers and rows.
     *
     * @param list<string> $headers Column header labels
     * @param list<list<string|int|float>> $rows Array of rows
     * @param array<string, mixed> $options
     */
    public function table(array $headers, array $rows, array $options = []): void
    {
        $s = $this->style;
        /** @var float $fontSize */
        $fontSize = $options['fontSize'] ?? $s->tableFontSize;
        $padding = $s->tableCellPadding;
        $headerFont = $s->tableHeaderFont;
        $bodyFont = FontMetrics::resolveFontName($s->fontFamily);
        /** @var bool $striped */
        $striped = $options['striped'] ?? $s->tableStriped;
        /** @var bool $repeatHeader */
        $repeatHeader = $options['repeatHeader'] ?? $s->tableRepeatHeaderOnNewPage;
        /** @var list<string> $aligns */
        $aligns = $options['columnAligns'] ?? [];
        $colCount = count($headers);

        /** @var list<float> $colWidths */
        $colWidths = $options['columnWidths'] ?? $this->autoColumnWidths(
            $headers, $rows, $fontSize, $headerFont, $bodyFont, $padding, $options
        );

        $lineHeight = $fontSize * $this->style->lineHeight;

        // Header renderer (reused on page breaks)
        $renderHeaderRow = function () use ($headers, $colWidths, $colCount, $fontSize, $headerFont, $padding, $lineHeight, $s, $aligns) {
            $rowHeight = $this->calcRowHeight($headers, $colWidths, $headerFont, $fontSize, $padding, $lineHeight);
            $this->ensureSpace($rowHeight);

            $x = $s->marginLeft;
            $y = $this->cursorY;

            $this->drawRect($x, $y - $rowHeight, array_sum($colWidths), $rowHeight, $s->tableHeaderBg);

            for ($c = 0; $c < $colCount; $c++) {
                $cellWidth = $colWidths[$c];
                $cellLines = FontMetrics::wordWrap($headers[$c], $headerFont, $fontSize, $cellWidth - (2 * $padding));
                $textY = $y - $padding - $fontSize;
                $align = $aligns[$c] ?? 'left';

                $this->setFontInStream($headerFont, $fontSize);
                $this->setColorInStream($s->tableHeaderColor);
                foreach ($cellLines as $line) {
                    $textX = $this->alignedX($x, $cellWidth, $padding, $line, $headerFont, $fontSize, $align);
                    $this->currentPageStream .= "BT {$this->fmt($textX)} {$this->fmt($textY)} Td ({$this->escape($line)}) Tj ET\n";
                    $textY -= $lineHeight;
                }
                $x += $cellWidth;
            }

            $this->drawRowBorders($s->marginLeft, $y, $colWidths, $rowHeight, $s->tableBorderColor);
            $this->cursorY = $y - $rowHeight;
        };

        $renderHeaderRow();

        foreach ($rows as $rowIdx => $row) {
            $cells = [];
            for ($c = 0; $c < $colCount; $c++) {
                $cells[] = isset($row[$c]) ? (string)$row[$c] : '';
            }

            $rowHeight = $this->calcRowHeight($cells, $colWidths, $bodyFont, $fontSize, $padding, $lineHeight);

            if ($this->cursorY - $rowHeight < $s->bottomY()) {
                $this->newPage();
                if ($repeatHeader) {
                    $renderHeaderRow();
                }
            }

            $x = $s->marginLeft;
            $y = $this->cursorY;

            $bgColor = ($striped && $rowIdx % 2 === 1) ? $s->tableAltRowBg : $s->tableRowBg;
            $this->drawRect($x, $y - $rowHeight, array_sum($colWidths), $rowHeight, $bgColor);

            for ($c = 0; $c < $colCount; $c++) {
                $cellWidth = $colWidths[$c];
                $cellLines = FontMetrics::wordWrap($cells[$c], $bodyFont, $fontSize, $cellWidth - (2 * $padding));
                $textY = $y - $padding - $fontSize;
                $align = $aligns[$c] ?? 'left';

                $this->setFontInStream($bodyFont, $fontSize);
                $this->setColorInStream($s->textColor);
                foreach ($cellLines as $line) {
                    $textX = $this->alignedX($x, $cellWidth, $padding, $line, $bodyFont, $fontSize, $align);
                    $this->currentPageStream .= "BT {$this->fmt($textX)} {$this->fmt($textY)} Td ({$this->escape($line)}) Tj ET\n";
                    $textY -= $lineHeight;
                }
                $x += $cellWidth;
            }

            $this->drawRowBorders($s->marginLeft, $y, $colWidths, $rowHeight, $s->tableBorderColor);
            $this->cursorY = $y - $rowHeight;
        }

        $this->cursorY -= $s->paragraphSpacing;
    }

    /**
     * Render a table from an array of associative arrays (e.g. Eloquent collections).
     *
     * @param list<array<string, mixed>> $data
     * @param array<string, mixed> $options
     */
    public function dataTable(array $data, array $options = []): void
    {
        if (empty($data)) return;

        /** @var list<string> $columns */
        $columns = $options['columns'] ?? array_keys($data[0]);
        /** @var array<string, string> $labels */
        $labels = $options['columnLabels'] ?? [];
        /** @var array<string, callable(mixed): string> $formatters */
        $formatters = $options['formatters'] ?? [];

        $headers = array_map(fn(string $col): string => $labels[$col] ?? ucfirst(str_replace('_', ' ', $col)), $columns);

        $rows = [];
        foreach ($data as $record) {
            $row = [];
            foreach ($columns as $col) {
                $val = $record[$col] ?? '';
                if (isset($formatters[$col])) {
                    $val = $formatters[$col]($val);
                }
                $row[] = is_scalar($val) ? (string) $val : '';
            }
            $rows[] = $row;
        }

        $this->table($headers, $rows, $options);
    }

    /**
     * Add a bold summary/totals row. Call immediately after table().
     *
     * @param list<string> $values
     * @param list<float> $colWidths
     * @param array<string, mixed> $options
     */
    public function summaryRow(array $values, array $colWidths, array $options = []): void
    {
        $s = $this->style;
        /** @var float $fontSize */
        $fontSize = $options['fontSize'] ?? $s->tableFontSize;
        $font = FontMetrics::resolveFontName($s->fontFamily, bold: true);
        $padding = $s->tableCellPadding;
        $lineHeight = $fontSize * $s->lineHeight;
        /** @var list<string> $aligns */
        $aligns = $options['columnAligns'] ?? [];
        /** @var list<float> $bgColor */
        $bgColor = $options['bgColor'] ?? [0.90, 0.92, 0.95];

        $rowHeight = $lineHeight + (2 * $padding);
        $this->ensureSpace($rowHeight);

        $x = $s->marginLeft;
        $y = $this->cursorY;

        $this->drawRect($x, $y - $rowHeight, array_sum($colWidths), $rowHeight, $bgColor);

        for ($c = 0; $c < count($colWidths); $c++) {
            $text = (string)($values[$c] ?? '');
            if ($text === '') { $x += $colWidths[$c]; continue; }

            $align = $aligns[$c] ?? 'left';
            $textX = $this->alignedX($x, $colWidths[$c], $padding, $text, $font, $fontSize, $align);
            $textY = $y - $padding - $fontSize;
            $this->setFontInStream($font, $fontSize);
            $this->setColorInStream($s->headingColor);
            $this->currentPageStream .= "BT {$this->fmt($textX)} {$this->fmt($textY)} Td ({$this->escape($text)}) Tj ET\n";
            $x += $colWidths[$c];
        }

        $this->drawRowBorders($s->marginLeft, $y, $colWidths, $rowHeight, $s->tableBorderColor);
        $this->cursorY = $y - $rowHeight;
    }

    // ─── Table Internals ──────────────────────────────────────────────

    /**
     * @param list<string> $headers
     * @param list<list<string|int|float>> $rows
     * @param array<string, mixed> $options
     * @return list<float>
     */
    private function autoColumnWidths(
        array $headers, array $rows, float $fontSize,
        string $headerFont, string $bodyFont, float $padding, array $options
    ): array {
        $colCount = count($headers);
        $availableWidth = $this->style->contentWidth();
        /** @var list<float> $minWidths */
        $minWidths = $options['columnMinWidths'] ?? [];
        /** @var float $maxColWidth */
        $maxColWidth = $options['maxColumnWidth'] ?? $availableWidth * 0.5;

        $naturalWidths = [];
        for ($c = 0; $c < $colCount; $c++) {
            $w = FontMetrics::stringWidth($headers[$c], $headerFont, $fontSize) + (2 * $padding);

            foreach ($rows as $row) {
                $val = isset($row[$c]) ? (string)$row[$c] : '';
                $cellW = FontMetrics::stringWidth($val, $bodyFont, $fontSize) + (2 * $padding);
                $w = max($w, $cellW);
            }

            $naturalWidths[$c] = min($w, $maxColWidth);
        }

        $totalNatural = array_sum($naturalWidths);

        if ($totalNatural <= $availableWidth) {
            $extra = $availableWidth - $totalNatural;
            $widths = [];
            for ($c = 0; $c < $colCount; $c++) {
                $share = ($naturalWidths[$c] / $totalNatural) * $extra;
                $widths[] = $naturalWidths[$c] + $share;
            }
            return $widths;
        }

        $widths = [];
        for ($c = 0; $c < $colCount; $c++) {
            $minW = $minWidths[$c] ?? 30;
            $proportional = ($naturalWidths[$c] / $totalNatural) * $availableWidth;
            $widths[] = max($proportional, $minW);
        }

        $total = array_sum($widths);
        if ($total > 0) {
            $scale = $availableWidth / $total;
            $widths = array_map(fn(float $w): float => $w * $scale, $widths);
        }

        return $widths;
    }

    /**
     * @param list<string> $cells
     * @param list<float> $colWidths
     */
    private function calcRowHeight(array $cells, array $colWidths, string $font, float $fontSize, float $padding, float $lineHeight): float
    {
        $maxLines = 1;
        for ($c = 0; $c < count($colWidths); $c++) {
            $text = (string) ($cells[$c] ?? '');
            $lines = FontMetrics::wordWrap($text, $font, $fontSize, $colWidths[$c] - (2 * $padding));
            $maxLines = max($maxLines, count($lines));
        }
        return ($maxLines * $lineHeight) + (2 * $padding);
    }

    private function alignedX(float $cellX, float $cellWidth, float $padding, string $text, string $font, float $fontSize, string $align): float
    {
        $textWidth = FontMetrics::stringWidth($text, $font, $fontSize);
        return match ($align) {
            'right'  => $cellX + $cellWidth - $padding - $textWidth,
            'center' => $cellX + ($cellWidth - $textWidth) / 2,
            default  => $cellX + $padding,
        };
    }

    /** @param list<float> $fillColor */
    private function drawRect(float $x, float $y, float $w, float $h, array $fillColor): void
    {
        $this->currentPageStream .= "{$this->colorStr($fillColor)} rg\n";
        $this->currentPageStream .= "{$this->fmt($x)} {$this->fmt($y)} {$this->fmt($w)} {$this->fmt($h)} re f\n";
        $this->activeColor = [-1.0, -1.0, -1.0];
    }

    /**
     * @param list<float> $colWidths
     * @param list<float> $borderColor
     */
    private function drawRowBorders(float $startX, float $topY, array $colWidths, float $rowHeight, array $borderColor): void
    {
        $this->currentPageStream .= "{$this->colorStr($borderColor)} RG {$this->fmt($this->style->tableLineWidth)} w\n";

        $totalWidth = array_sum($colWidths);
        $bottomY = $topY - $rowHeight;

        $this->currentPageStream .= "{$this->fmt($startX)} {$this->fmt($topY)} m {$this->fmt($startX + $totalWidth)} {$this->fmt($topY)} l S\n";
        $this->currentPageStream .= "{$this->fmt($startX)} {$this->fmt($bottomY)} m {$this->fmt($startX + $totalWidth)} {$this->fmt($bottomY)} l S\n";

        $x = $startX;
        for ($c = 0; $c <= count($colWidths); $c++) {
            $this->currentPageStream .= "{$this->fmt($x)} {$this->fmt($topY)} m {$this->fmt($x)} {$this->fmt($bottomY)} l S\n";
            if ($c < count($colWidths)) {
                $x += $colWidths[$c];
            }
        }
    }

    // ─── Images ───────────────────────────────────────────────────────

    /**
     * Insert a JPEG image from a file path.
     *
     * @param array<string, mixed> $options
     */
    public function image(string $path, ?float $width = null, ?float $height = null, array $options = []): void
    {
        if (!is_file($path)) {
            throw new \RuntimeException("Cannot read image file: {$path}");
        }

        $data = file_get_contents($path);
        if ($data === false) {
            throw new \RuntimeException("Cannot read image file: {$path}");
        }
        $this->imageFromString($data, $width, $height, $options);
    }

    /**
     * Insert a JPEG image from raw binary data.
     *
     * @param array<string, mixed> $options
     */
    public function imageFromString(string $data, ?float $width = null, ?float $height = null, array $options = []): void
    {
        $img = $this->embedImage($data);

        /** @var float $dpi */
        $dpi = $options['dpi'] ?? 72.0;
        $naturalWidth = ($img['pixelWidth'] / $dpi) * 72.0;
        $naturalHeight = ($img['pixelHeight'] / $dpi) * 72.0;

        if ($width !== null && $height !== null) {
            $displayWidth = $width;
            $displayHeight = $height;
        } elseif ($width !== null) {
            $displayWidth = $width;
            $displayHeight = $width * ($img['pixelHeight'] / $img['pixelWidth']);
        } elseif ($height !== null) {
            $displayHeight = $height;
            $displayWidth = $height * ($img['pixelWidth'] / $img['pixelHeight']);
        } else {
            $displayWidth = $naturalWidth;
            $displayHeight = $naturalHeight;
        }

        // Clamp to content width
        $maxWidth = $this->style->contentWidth();
        if ($displayWidth > $maxWidth) {
            $scale = $maxWidth / $displayWidth;
            $displayWidth = $maxWidth;
            $displayHeight *= $scale;
        }

        // Clamp to content height (prevent infinite page-break loop)
        $maxHeight = $this->style->contentHeight();
        if ($displayHeight > $maxHeight) {
            $scale = $maxHeight / $displayHeight;
            $displayHeight = $maxHeight;
            $displayWidth *= $scale;
        }

        $this->ensureSpace($displayHeight);

        // X position based on alignment
        /** @var string $align */
        $align = $options['align'] ?? 'left';
        $x = match ($align) {
            'center' => $this->style->marginLeft + ($maxWidth - $displayWidth) / 2,
            'right'  => $this->style->pageWidth - $this->style->marginRight - $displayWidth,
            default  => $this->style->marginLeft,
        };

        // PDF images are positioned by bottom-left corner
        $y = $this->cursorY - $displayHeight;

        // Draw: save state, transform, paint XObject, restore state
        $this->currentPageStream .= "q\n";
        $this->currentPageStream .= "{$this->fmt($displayWidth)} 0 0 {$this->fmt($displayHeight)} {$this->fmt($x)} {$this->fmt($y)} cm\n";
        $this->currentPageStream .= "/{$img['name']} Do\n";
        $this->currentPageStream .= "Q\n";

        if (!in_array($img['name'], $this->pageImages, true)) {
            $this->pageImages[] = $img['name'];
        }

        $this->cursorY -= $displayHeight;
        $this->cursorY -= $this->style->paragraphSpacing;
    }

    /**
     * Insert a JPEG image from a Laravel filesystem disk (S3, local, etc.).
     *
     * Accepts any object with a get(string $path): ?string method — compatible
     * with Laravel's Storage::disk() without requiring a hard dependency.
     *
     * Usage: $pdf->imageFromDisk(Storage::disk('s3'), 'photos/logo.jpg', width: 200)
     */
    /** @param array<string, mixed> $options */
    public function imageFromDisk(object $disk, string $path, ?float $width = null, ?float $height = null, array $options = []): void
    {
        if (!method_exists($disk, 'get')) {
            throw new \InvalidArgumentException(
                'The disk object must have a get(string $path): ?string method. ' .
                "Laravel's Storage::disk() returns a compatible object."
            );
        }

        /** @var string|null|false $data */
        $data = $disk->get($path);

        if ($data === null || $data === false) {
            throw new \RuntimeException("Cannot read image from disk at path: {$path}");
        }

        $this->imageFromString($data, $width, $height, $options);
    }

    /**
     * Embed JPEG data into the document and return its reference info.
     * Deduplicates by content hash — the same image is stored only once.
     *
     * @return array{name: string, pixelWidth: int, pixelHeight: int}
     */
    private function embedImage(string $data): array
    {
        $hash = hash('sha256', $data);
        if (isset($this->images[$hash])) {
            $img = $this->images[$hash];
            return ['name' => $img['name'], 'pixelWidth' => $img['width'], 'pixelHeight' => $img['height']];
        }

        $meta = $this->parseJpegMetadata($data);

        $this->imageCounter++;
        $name = "Im{$this->imageCounter}";
        $objId = $this->doc->allocateObject();

        $length = strlen($data);
        $dict = "<< /Type /XObject /Subtype /Image" .
            " /Width {$meta['width']}" .
            " /Height {$meta['height']}" .
            " /ColorSpace {$meta['colorSpace']}" .
            " /BitsPerComponent {$meta['bitsPerComponent']}" .
            " /Filter /DCTDecode" .
            " /Length {$length} >>";

        $this->doc->setObject($objId, "{$dict}\nstream\n{$data}\nendstream");

        $this->images[$hash] = [
            'objId' => $objId,
            'width' => $meta['width'],
            'height' => $meta['height'],
            'name' => $name,
        ];

        return ['name' => $name, 'pixelWidth' => $meta['width'], 'pixelHeight' => $meta['height']];
    }

    /**
     * Parse JPEG binary data to extract dimensions and color space.
     * No GD dependency — reads SOF markers directly.
     *
     * @return array{width: int, height: int, colorSpace: string, bitsPerComponent: int}
     */
    private function parseJpegMetadata(string $data): array
    {
        if (strlen($data) < 2 || ord($data[0]) !== 0xFF || ord($data[1]) !== 0xD8) {
            throw new \RuntimeException('Invalid JPEG data: missing SOI marker.');
        }

        $offset = 2;
        $length = strlen($data);

        while ($offset < $length - 1) {
            if (ord($data[$offset]) !== 0xFF) {
                throw new \RuntimeException('Invalid JPEG structure at offset ' . $offset);
            }

            $marker = ord($data[$offset + 1]);

            if ($marker === 0xFF) {
                $offset++;
                continue;
            }

            // SOF0, SOF1, SOF2 — Start of Frame markers contain dimensions
            if (in_array($marker, [0xC0, 0xC1, 0xC2], true)) {
                if ($offset + 9 >= $length) {
                    throw new \RuntimeException('Truncated JPEG SOF segment.');
                }

                $bitsPerComponent = ord($data[$offset + 4]);
                $height = (ord($data[$offset + 5]) << 8) | ord($data[$offset + 6]);
                $width = (ord($data[$offset + 7]) << 8) | ord($data[$offset + 8]);
                $channels = ord($data[$offset + 9]);

                $colorSpace = match ($channels) {
                    1 => '/DeviceGray',
                    3 => '/DeviceRGB',
                    4 => '/DeviceCMYK',
                    default => '/DeviceRGB',
                };

                return [
                    'width' => $width,
                    'height' => $height,
                    'colorSpace' => $colorSpace,
                    'bitsPerComponent' => $bitsPerComponent,
                ];
            }

            if ($marker === 0xD9) {
                break;
            }

            // Standalone markers (RST0-RST7, stuffed byte)
            if ($marker === 0x00 || ($marker >= 0xD0 && $marker <= 0xD7)) {
                $offset += 2;
                continue;
            }

            // Skip segment by reading its length
            if ($offset + 3 >= $length) {
                break;
            }
            $segmentLength = (ord($data[$offset + 2]) << 8) | ord($data[$offset + 3]);
            $offset += 2 + $segmentLength;
        }

        throw new \RuntimeException('Could not find SOF marker in JPEG data. The file may be corrupted or not a JPEG.');
    }

    // ─── Output ───────────────────────────────────────────────────────

    /** Generate the final PDF bytes. */
    public function output(): string
    {
        if (!$this->documentFinished) {
            $this->finalizePage();
            $this->documentFinished = true;
        }

        $totalPages = (string)$this->pageCount;

        foreach ($this->pageStreams as $contentObjId => $stream) {
            $stream = str_replace('___TOTAL_PAGES___', $totalPages, $stream);

            if ($this->style->compress) {
                $compressed = gzcompress($stream) ?: '';
                $length = strlen($compressed);
                $this->doc->setObject($contentObjId, "<< /Length {$length} /Filter /FlateDecode >>\nstream\n{$compressed}\nendstream");
            } else {
                $length = strlen($stream);
                $this->doc->setObject($contentObjId, "<< /Length {$length} >>\nstream\n{$stream}\nendstream");
            }
        }

        return $this->doc->output();
    }

    /** Save the PDF to a file path. */
    public function save(string $path): void
    {
        file_put_contents($path, $this->output());
    }

    /** Get the current page number. */
    public function getCurrentPage(): int
    {
        return $this->pageCount;
    }

    /** Get remaining vertical space on the current page (in points). */
    public function getRemainingSpace(): float
    {
        return $this->cursorY - $this->style->bottomY();
    }
}
