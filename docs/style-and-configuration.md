---
title: "Style & Configuration"
description: "Control every visual aspect of your PDF with the Style class — page size, fonts, colors, tables, headers, and footers."
order: 6
---

# Style & Configuration

The `Style` class controls every visual aspect of your PDF. Pass it to `FlatPdf::make()` to customize the output.

## Presets

Start with a preset and optionally override specific properties.

```php
use PdfParse\FlatPdf\Style;

$pdf = FlatPdf::make(Style::a4());              // A4 paper (595.28 x 841.89pt)
$pdf = FlatPdf::make(Style::compact());          // Dense layout, smaller fonts
$pdf = FlatPdf::make(Style::landscape());        // Landscape letter (792 x 612pt)
$pdf = FlatPdf::make(Style::landscapeCompact()); // Landscape + compact
```

## All Parameters

All parameters with their defaults:

```php
Style::make(
    // Page dimensions (72 points = 1 inch)
    pageWidth: 612,             // Letter width (A4: 595.28)
    pageHeight: 792,            // Letter height (A4: 841.89)
    marginTop: 60,
    marginBottom: 60,
    marginLeft: 50,
    marginRight: 50,

    // Body text
    fontFamily: 'Helvetica',    // 'Times-Roman', 'Courier'
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
    compress: true,
);
```

## Colors

Colors use RGB arrays with values from `0.0` to `1.0`.

```php
[1.0, 0.0, 0.0]  // Red
[0.0, 0.0, 0.0]  // Black
[1.0, 1.0, 1.0]  // White
[0.22, 0.40, 0.65] // Steel blue (default header)
```

## Custom Style Example

```php
$style = Style::make(
    pageWidth: 595.28,
    pageHeight: 841.89,
    fontFamily: 'Times-Roman',
    tableHeaderBg: [0.1, 0.1, 0.1],
    tableStriped: false,
    headerText: 'Confidential',
    footerText: 'Acme Corp',
);

$pdf = FlatPdf::make($style);
```
