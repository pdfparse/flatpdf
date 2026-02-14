---
title: "Page Control"
description: "Control page breaks, check remaining space, and configure headers, footers, and page numbers."
order: 7
---

# Page Control

FlatPDF gives you control over page breaks, remaining space, and page numbering.

## Page Breaks

```php
$pdf->pageBreak();  // Force a new page
$pdf->newPage();    // Alias for pageBreak()
```

## Remaining Space

Check how much vertical space is left on the current page before adding content.

```php
$remaining = $pdf->getRemainingSpace(); // Points remaining

if ($remaining < 100) {
    $pdf->pageBreak();
}

$pdf->h2('This section needs space');
```

## Current Page Number

```php
$page = $pdf->getCurrentPage(); // Returns current page number (1-based)
```

## Headers & Footers

Configure headers, footers, and page numbers via the Style class.

```php
$style = Style::make(
    // Page numbers
    showPageNumbers: true,
    pageNumberFormat: 'Page {page} of {pages}',

    // Custom header and footer text
    headerText: 'Monthly Report - Q4 2025',
    footerText: 'Acme Corporation',

    // Styling
    headerFooterFontSize: 7,
    headerFooterColor: [0.5, 0.5, 0.5],
);

$pdf = FlatPdf::make($style);
```

The `{page}` and `{pages}` placeholders in `pageNumberFormat` are replaced with the current page and total pages when the PDF is finalized.
