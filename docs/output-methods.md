---
title: "Output Methods"
description: "Save PDFs to disk, get raw bytes, return Laravel HTTP responses, upload to S3, and control compression."
order: 8
---

# Output Methods

FlatPDF gives you two ways to get the generated PDF: as raw bytes or written to disk.

## Save to Disk

```php
$pdf->save('report.pdf');
$pdf->save('/absolute/path/to/report.pdf');
```

## Get Raw Bytes

The `output()` method returns the complete PDF as a string.

```php
$bytes = $pdf->output();
```

## Laravel HTTP Response

```php
// Download
return response($pdf->output())
    ->header('Content-Type', 'application/pdf')
    ->header('Content-Disposition', 'attachment; filename="report.pdf"');

// Inline (display in browser)
return response($pdf->output())
    ->header('Content-Type', 'application/pdf')
    ->header('Content-Disposition', 'inline; filename="report.pdf"');
```

## Upload to S3

```php
use Illuminate\Support\Facades\Storage;

Storage::disk('s3')->put(
    'reports/monthly.pdf',
    $pdf->output()
);
```

## Compression

Stream compression is enabled by default, reducing typical documents by 60–70%. Disable it for debugging.

```php
$style = Style::make(compress: false);
$pdf = FlatPdf::make($style);

// PDF streams will be uncompressed (larger but human-readable)
```
