---
title: "Tables"
description: "Render tables with automatic column sizing, page breaks, repeating headers, and summary rows."
order: 3
---

# Tables

Tables are FlatPDF's core strength. They handle hundreds of rows efficiently with automatic column sizing, page breaks, and repeating headers.

## Basic Table

```php
$pdf->table(
    headers: ['Name', 'Email', 'Role'],
    rows: [
        ['Alice Johnson', 'alice@example.com', 'Admin'],
        ['Bob Smith', 'bob@example.com', 'Editor'],
        ['Carol White', 'carol@example.com', 'Viewer'],
    ]
);
```

## Table Options

Pass an options array as the third argument to customize table behavior.

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `columnAligns` | string[] | all 'left' | 'left', 'center', or 'right' per column |
| `columnWidths` | float[] | auto | Explicit widths in points |
| `columnMinWidths` | float[] | [] | Minimum width per column |
| `maxColumnWidth` | float | 50% content | Cap width for any column |
| `fontSize` | float | Style default | Override font size for this table |
| `striped` | bool | true | Alternating row background colors |
| `repeatHeader` | bool | true | Repeat headers on page breaks |

## Column Alignment

```php
$pdf->table(
    headers: ['Product', 'Quantity', 'Price', 'Total'],
    rows: $rows,
    options: [
        'columnAligns' => ['left', 'right', 'right', 'right'],
        'striped' => true,
        'repeatHeader' => true,
    ]
);
```

## Multi-Page Tables

When a table spans multiple pages, FlatPDF automatically:

- Breaks cleanly between rows (never mid-row)
- Repeats headers on each new page (when `repeatHeader` is true)
- Maintains striping continuity across pages

## Summary Row

```php
$colWidths = $pdf->table($headers, $rows);

$pdf->summaryRow(
    values: ['', '', 'Total:', '$36,400'],
    colWidths: $colWidths,
    options: ['columnAligns' => ['left', 'left', 'right', 'right']]
);
```

The `table()` method returns the calculated column widths, which you can pass to `summaryRow()` for alignment.
