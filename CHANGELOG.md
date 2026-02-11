# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com), and this project adheres to [Semantic Versioning](https://semver.org).

## [Unreleased]

## [0.1.0] - 2025-02-11

### Added

- Core PDF generation with `FlatPdf` class
- Text rendering with automatic word-wrapping (`text`, `bold`, `italic`, `code`)
- Headings (`h1`, `h2`, `h3`) with configurable sizing and underlines
- Table rendering with auto-sizing columns, word-wrapping, and striped rows
- `dataTable()` for associative arrays (Eloquent-friendly)
- `summaryRow()` for totals rows
- Repeated table headers on page breaks
- JPEG image embedding from file path, raw bytes, or Laravel Storage disk
- Image deduplication by SHA-256 hash
- Automatic page breaks with header/footer rendering
- Page numbers with `{page} of {pages}` substitution
- `Style` class with 30+ configurable properties
- Style presets: `compact()`, `a4()`, `landscape()`, `landscapeCompact()`
- FlateDecode stream compression (60-70% size reduction)
- Standard 14 PDF font support with font aliases
- `output()` for raw bytes and `save()` for file writing
