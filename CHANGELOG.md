# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com), and this project adheres to [Semantic Versioning](https://semver.org).

## [0.1.5] - 2025-02-14

### Added

- Documentation site with dedicated pages for getting started, text and headings, tables, data tables, images, page control, output methods, and style configuration
- `llms.txt` and `llms-full.txt` for LLM-friendly package documentation
- `docs/`, `llms.txt`, and `llms-full.txt` to `.gitattributes` export-ignore

## [0.1.4] - 2025-02-13

### Changed

- Added package homepage (`https://flatpdf.pdfparse.co/`) to `composer.json` and README

## [0.1.3] - 2025-02-12

### Added

- `FlatPdf::make()` static factory method for Laravel-style instantiation
- `Style::make()` static factory method — accepts the same named arguments as the constructor

## [0.1.2] - 2025-02-11

### Added

- `Encoding` class for automatic UTF-8 to Windows-1252 conversion — characters like `€`, `²`, `°`, `—`, `©`, `£`, accented letters now render correctly instead of appearing garbled
- Extended character width metrics for Helvetica and Helvetica-Bold (Euro, em/en dash, smart quotes, bullet, trademark, copyright, degree, superscripts, and more)

### Fixed

- UTF-8 multi-byte characters (e.g. `²` → `Â²`, `€` → `â‚¬`, `—` → `â€"`) now automatically convert to correct single-byte WinAnsiEncoding before entering the PDF stream
- Word-wrap and column width calculations now use correct single-byte widths for non-ASCII characters
- Characters outside the Windows-1252 range (e.g. `₹`, emoji) gracefully degrade to `?` instead of producing garbled bytes

## [0.1.1] - 2025-02-11

### Fixed

- Table rows after the first on each page rendering invisible due to `drawRect()` changing the PDF fill color without invalidating the cached text color state
- `{pages}` total page count placeholder showing literal `___TOTAL_PAGES___` when stream compression is enabled, by deferring compression until after placeholder replacement in `output()`
- Replaced UTF-8 em dashes in demo with ASCII dashes to avoid garbled output with WinAnsiEncoding Type1 fonts

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
