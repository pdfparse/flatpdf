---
title: "Images"
description: "Embed JPEG images from file paths, raw bytes, or Laravel Storage disks."
order: 5
---

# Images

FlatPDF supports JPEG images embedded directly into the PDF. Images can be loaded from file paths, raw bytes, or Laravel Storage disks.

> [!WARNING]
> **Note:** Only JPEG format is supported. PNG, GIF, and other formats are not available. Convert images to JPEG before embedding.

## From File Path

```php
$pdf->image('/path/to/photo.jpg', width: 300);

// With height (aspect ratio is maintained if only one dimension is given)
$pdf->image('/path/to/photo.jpg', width: 300, height: 200);
```

## From Raw Bytes

Use `imageFromString()` when you have the image data in memory.

```php
$jpeg = file_get_contents('https://example.com/photo.jpg');
$pdf->imageFromString($jpeg, width: 250);
```

## From Laravel Storage

Works with any Laravel filesystem disk (local, S3, etc.).

```php
use Illuminate\Support\Facades\Storage;

$pdf->imageFromDisk(Storage::disk('s3'), 'reports/chart.jpg', width: 400);
```

## Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `dpi` | float | 72.0 | Image resolution |
| `align` | string | 'left' | 'left', 'center', or 'right' |

```php
$pdf->image('/path/to/logo.jpg', width: 150, options: [
    'align' => 'center',
    'dpi' => 150,
]);
```
