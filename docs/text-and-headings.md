---
title: "Text & Headings"
description: "Render headings, body text, bold, italic, and monospaced text with automatic word wrapping."
order: 2
---

# Text & Headings

FlatPDF provides methods for rendering text content with various styles. All text wraps automatically within the page margins.

## Headings

Three heading levels are available, each with configurable sizes via the Style class.

```php
$pdf->h1('Large Heading');     // Default 20pt, with underline
$pdf->h2('Medium Heading');    // Default 15pt
$pdf->h3('Small Heading');     // Default 12pt
```

## Body Text

The `text()` method renders body text with automatic word-wrapping. You can override the font, size, and color.

```php
$pdf->text('Regular body text with word wrapping.');

// With overrides
$pdf->text('Custom styled text', font: 'Courier', size: 12, color: [0.5, 0.0, 0.0]);
```

## Styled Text

```php
$pdf->bold('Bold text');
$pdf->italic('Italic text');
$pdf->code('Monospaced code text');

// Bold and italic accept optional size and color
$pdf->bold('Large bold', size: 14);
$pdf->italic('Red italic', color: [0.8, 0.0, 0.0]);
```

## Spacing & Separators

```php
$pdf->space(20);    // Add 20 points of vertical space
$pdf->hr();         // Horizontal rule across the content width
```

## Available Fonts

FlatPDF uses the 14 standard PDF fonts (no embedding required):

| Family | Variants |
|--------|----------|
| Helvetica | Helvetica, Helvetica-Bold, Helvetica-Oblique, Helvetica-BoldOblique |
| Times-Roman | Times-Roman, Times-Bold, Times-Italic, Times-BoldItalic |
| Courier | Courier, Courier-Bold, Courier-Oblique, Courier-BoldOblique |

Aliases `arial`, `sans-serif`, `serif`, and `monospace` are also supported.
