---
title: "Getting Started"
description: "Install FlatPDF and generate your first PDF in minutes."
order: 1
---

# Getting Started

FlatPDF is a flat, zero-dependency PHP PDF writer built for speed and large documents. Instead of converting HTML/CSS to PDF, it writes PDF operators directly — making it ideal for table-heavy reports.

## When to use FlatPDF

- Reports with hundreds or thousands of table rows
- Invoices, catalogs, and financial statements
- Server-side PDF generation where speed and memory matter
- Projects that need zero external dependencies

## Requirements

- PHP 8.2 or higher
- No extensions required

## Installation

```bash
composer require pdfparse/flatpdf
```

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

This produces a PDF with a heading, body text, and a formatted table with right-aligned numbers — all in a few milliseconds.

> [!TIP]
> **Static `make()` vs `new`:** Both `FlatPdf::make()` and `new FlatPdf()` are supported. The static factory is the recommended approach and allows fluent-style usage. The same applies to `Style::make()` vs `new Style()`.

```php
// Static factory (recommended)
$pdf = FlatPdf::make();
$pdf = FlatPdf::make(Style::a4());

// Constructor (also works)
$pdf = new FlatPdf();
$pdf = new FlatPdf(Style::a4());
```

> [!TIP]
> **Try it live:** Head to the [Playground](/playground) to generate PDFs interactively with real data.
