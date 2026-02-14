---
title: "Data Tables"
description: "Generate tables from associative arrays or Eloquent collections with automatic column extraction and formatters."
order: 4
---

# Data Tables

The `dataTable()` method accepts associative arrays or Eloquent collections, automatically extracting columns and labels.

## Basic Usage

```php
$users = [
    ['name' => 'Alice', 'email' => 'alice@example.com', 'role' => 'Admin'],
    ['name' => 'Bob', 'email' => 'bob@example.com', 'role' => 'Editor'],
];

$pdf->dataTable($users);
```

Without options, all keys from the first record are used as columns, and the keys become header labels.

## With Options

```php
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

## Options

| Option | Type | Description |
|--------|------|-------------|
| `columns` | string[] | Which keys to include; defaults to all keys from first record |
| `columnLabels` | array<string, string> | Custom display labels per column key |
| `formatters` | array<string, callable> | Value formatters per column key |
| + all table() options | — | striped, repeatHeader, columnAligns, etc. are all passed through |

## Eloquent Integration

```php
$users = User::all()->toArray();

$pdf->dataTable($users, [
    'columns' => ['name', 'email', 'created_at'],
    'columnLabels' => ['created_at' => 'Joined'],
    'formatters' => [
        'created_at' => fn($v) => Carbon::parse($v)->format('M j, Y'),
    ],
]);
```
