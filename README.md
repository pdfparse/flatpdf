# FlatPdf

A flat, zero-dependency PHP PDF writer built for speed and large documents.

Built by [PdfParse](https://pdfparse.co). [Documentation](https://flatpdf.pdfparse.co/)

## Why FlatPdf?

DOMPDF and mPDF are great for converting HTML/CSS to PDF, but they choke on large table-heavy documents — slow rendering, high memory usage, and unpredictable page-break behavior inside tables.

FlatPdf writes PDF operators directly. No HTML parsing, no CSS rendering engine, no external binaries. Just PHP generating PDF bytes.

**Good at:**
- Long reports with hundreds/thousands of table rows
- Consistent, predictable table rendering across pages
- Auto-sizing columns based on content
- Repeated headers on page breaks
- JPEG images with Laravel Storage/S3 support
- Stream compression (FlateDecode) — 60-70% smaller files
- Low memory usage — streams page-by-page
- Fast — generates a 12-page financial report in milliseconds

**Not for:**
- Pixel-perfect HTML/CSS reproduction
- Complex layouts (floats, grids, absolute positioning)
- Custom font embedding (uses the 14 standard PDF fonts)
- PNG/GIF images (JPEG only)

## Installation

```bash
composer require pdfparse/flatpdf
```

Requires PHP 8.2+.

## Quick Start

```php
use PdfParse\FlatPdf\FlatPdf;
use PdfParse\FlatPdf\Style;

$pdf = FlatPdf::make();

$pdf->h1('Monthly Report');
$pdf->text('Generated on ' . date('Y-m-d'));

$pdf->table(
    headers: ['Product', 'Revenue', 'Margin'],
    rows: [
        ['Widget A', '$12,400', '34%'],
        ['Widget B', '$8,200', '41%'],
        ['Widget C', '$15,800', '28%'],
    ],
    options: [
        'columnAligns' => ['left', 'right', 'right'],
    ]
);

$pdf->save('report.pdf');
```

## Style Presets

```php
// Dense layout — maximum data per page
$pdf = FlatPdf::make(Style::compact());

// A4 paper
$pdf = FlatPdf::make(Style::a4());

// Landscape — great for wide tables
$pdf = FlatPdf::make(Style::landscape());

// Landscape + compact — maximum columns
$pdf = FlatPdf::make(Style::landscapeCompact());

// Custom style
$pdf = FlatPdf::make(Style::make(
    headerText: 'Acme Corp — Confidential',
    footerText: 'Internal Use Only',
    tableHeaderBg: [0.18, 0.33, 0.59],
    tableFontSize: 7,
    showPageNumbers: true,
));
```

## Tables

### Basic table

```php
$pdf->table(
    ['Name', 'Department', 'Salary'],
    [
        ['Alice', 'Engineering', '$150,000'],
        ['Bob', 'Sales', '$120,000'],
    ],
    [
        'columnAligns' => ['left', 'left', 'right'],
        'striped' => true,
        'repeatHeader' => true,
    ]
);
```

### From associative arrays (Eloquent-friendly)

```php
$users = User::all()->toArray();

$pdf->dataTable($users, [
    'columns' => ['name', 'email', 'created_at', 'subscription_amount'],
    'columnLabels' => [
        'name' => 'Full Name',
        'created_at' => 'Joined',
        'subscription_amount' => 'MRR',
    ],
    'formatters' => [
        'created_at' => fn($v) => date('M j, Y', strtotime($v)),
        'subscription_amount' => fn($v) => '$' . number_format($v / 100, 2),
    ],
    'columnAligns' => ['left', 'left', 'center', 'right'],
]);
```

### Table options

| Option | Type | Description |
|--------|------|-------------|
| `columnWidths` | `float[]` | Explicit column widths in points |
| `columnAligns` | `string[]` | `'left'`, `'right'`, or `'center'` per column |
| `columnMinWidths` | `float[]` | Minimum widths per column |
| `maxColumnWidth` | `float` | Cap any single column width |
| `fontSize` | `float` | Override table font size |
| `striped` | `bool` | Alternating row background colors |
| `repeatHeader` | `bool` | Repeat header row after page breaks |

## Text & Headings

```php
$pdf->h1('Section Title');        // Large heading with underline
$pdf->h2('Subsection');           // Medium heading
$pdf->h3('Sub-subsection');       // Small heading

$pdf->text('Regular paragraph text with automatic word-wrapping.');
$pdf->bold('Bold text.');
$pdf->italic('Italic text.');
$pdf->code('Monospaced text.');

$pdf->hr();                       // Horizontal rule
$pdf->space(20);                  // Vertical space (points)
$pdf->pageBreak();                // Force new page
```

## Images

JPEG images are embedded directly as DCTDecode streams — no re-encoding, no GD dependency.

### From a file path

```php
$pdf->image('/path/to/photo.jpg', width: 300);
```

### From raw bytes

```php
$jpeg = file_get_contents('https://example.com/photo.jpg');
$pdf->imageFromString($jpeg, width: 250);
```

### From a Laravel filesystem disk (S3, etc.)

```php
$pdf->imageFromDisk(Storage::disk('s3'), 'reports/chart.jpg', width: 400);

// Also works with any disk: local, GCS, etc.
$pdf->imageFromDisk(Storage::disk('public'), 'images/logo.jpg', width: 150);
```

`imageFromDisk()` accepts any object with a `get(string $path): ?string` method — fully compatible with Laravel's `Storage::disk()` without requiring Laravel as a dependency.

### Sizing and alignment

```php
// Specify width — height auto-calculated to preserve aspect ratio
$pdf->image('photo.jpg', width: 200);

// Specify height — width auto-calculated
$pdf->image('photo.jpg', height: 150);

// Specify both (may distort)
$pdf->image('photo.jpg', width: 200, height: 100);

// No dimensions — renders at natural size (1 pixel = 1 point at 72 DPI)
$pdf->image('photo.jpg');

// Alignment
$pdf->image('photo.jpg', width: 200, options: ['align' => 'center']);
$pdf->image('photo.jpg', width: 200, options: ['align' => 'right']);

// Custom DPI (default 72)
$pdf->image('photo.jpg', options: ['dpi' => 150]);
```

Images auto-clamp to the content area and trigger page breaks when they don't fit.

## Page Control

```php
// Check remaining space before adding content
if ($pdf->getRemainingSpace() < 100) {
    $pdf->pageBreak();
}

// Get current page number
echo $pdf->getCurrentPage();
```

## Saving to S3 / Laravel Filesystems

`output()` returns the raw PDF bytes — pass them directly to any filesystem or HTTP response:

```php
// Save to S3
Storage::disk('s3')->put('reports/monthly.pdf', $pdf->output());

// Save to any disk
Storage::disk('gcs')->put('exports/report.pdf', $pdf->output());

// Return as HTTP download
return response($pdf->output())
    ->header('Content-Type', 'application/pdf')
    ->header('Content-Disposition', 'attachment; filename="report.pdf"');

// Inline browser preview
return response($pdf->output())
    ->header('Content-Type', 'application/pdf')
    ->header('Content-Disposition', 'inline; filename="report.pdf"');

// Save locally and to S3
$pdf->save(storage_path('app/report.pdf'));
Storage::disk('s3')->put('reports/report.pdf', $pdf->output());
```

### Full Laravel example

```php
use PdfParse\FlatPdf\FlatPdf;
use Illuminate\Support\Facades\Storage;

public function generateReport()
{
    $pdf = FlatPdf::make();

    // Load images from S3
    $pdf->imageFromDisk(Storage::disk('s3'), 'assets/logo.jpg', width: 150);

    $pdf->h1('Monthly Report');
    $pdf->dataTable(Order::all()->toArray(), [
        'columns' => ['id', 'customer', 'total', 'created_at'],
        'formatters' => [
            'total' => fn($v) => '$' . number_format($v / 100, 2),
        ],
    ]);

    // Save back to S3
    Storage::disk('s3')->put('reports/monthly.pdf', $pdf->output());

    return response($pdf->output())
        ->header('Content-Type', 'application/pdf');
}
```

## Style Reference

All `Style` properties with defaults:

```php
Style::make(
    // Page dimensions (points: 72 points = 1 inch)
    pageWidth: 612,          // Letter width (A4: 595.28)
    pageHeight: 792,         // Letter height (A4: 841.89)
    marginTop: 60,
    marginBottom: 60,
    marginLeft: 50,
    marginRight: 50,

    // Body text
    fontFamily: 'Helvetica', // Also: 'Times-Roman', 'Courier'
    fontSize: 9,
    lineHeight: 1.4,
    textColor: [0.2, 0.2, 0.2],

    // Headings
    h1Size: 20,
    h2Size: 15,
    h3Size: 12,
    headingSpaceBefore: 16,
    headingSpaceAfter: 6,
    headingColor: [0.1, 0.1, 0.1],

    // Tables
    tableFontSize: 8,
    tableCellPadding: 5,
    tableLineWidth: 0.5,
    tableHeaderBg: [0.22, 0.40, 0.65],
    tableHeaderColor: [1.0, 1.0, 1.0],
    tableHeaderFont: 'Helvetica-Bold',
    tableRowBg: [1.0, 1.0, 1.0],
    tableAltRowBg: [0.95, 0.96, 0.98],
    tableBorderColor: [0.78, 0.80, 0.83],
    tableStriped: true,
    tableRepeatHeaderOnNewPage: true,

    // Header / Footer
    showPageNumbers: true,
    pageNumberFormat: 'Page {page} of {pages}',
    headerFooterFontSize: 7,
    headerFooterColor: [0.5, 0.5, 0.5],
    headerText: '',
    footerText: '',

    // Spacing
    paragraphSpacing: 8,

    // Compression
    compress: true,           // FlateDecode stream compression (default: on)
);
```

Colors are RGB arrays with values from 0.0 to 1.0.

## Compression

Stream compression is enabled by default. Content streams are compressed with `gzcompress()` (zlib deflate), which typically reduces file size by 60-70% on table-heavy documents.

```php
// Compression is on by default — nothing to do
$pdf = FlatPdf::make();

// Disable if you need readable PDF internals for debugging
$pdf = FlatPdf::make(Style::make(compress: false));
```

JPEG image streams are never double-compressed — they use DCTDecode only.

## Available Fonts

FlatPdf uses the 14 standard PDF fonts (no embedding required, supported by every PDF reader):

- **Helvetica** (+ Bold, Oblique, BoldOblique)
- **Times-Roman** (+ Bold, Italic, BoldItalic)
- **Courier** (+ Bold, Oblique, BoldOblique)

Aliases work too: `'arial'`, `'sans-serif'`, `'serif'`, `'monospace'`.

## Testing & Static Analysis

FlatPdf uses [Pest](https://pestphp.com) for testing and [PHPStan](https://phpstan.org) at max level for static analysis.

```bash
# Install dev dependencies
composer install

# Run the test suite
composer test

# Run tests with coverage
composer test:coverage

# Run PHPStan static analysis
composer analyse

# Run both tests and analysis
composer qa
```

## Releasing a New Version

1. Update your code and merge to `main`
2. Tag the release with a semantic version:
   ```bash
   git tag v1.0.0
   git push origin v1.0.0
   ```
3. The GitHub Actions workflow will automatically notify Packagist
4. The new version will be available via `composer require pdfparse/flatpdf` within minutes

### Version Guidelines

- **Patch** (`v1.0.1`) — Bug fixes, no breaking changes
- **Minor** (`v1.1.0`) — New features, backwards compatible
- **Major** (`v2.0.0`) — Breaking changes to the public API

## License

MIT
