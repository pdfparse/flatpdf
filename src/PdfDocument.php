<?php

declare(strict_types=1);

namespace PdfParse\FlatPdf;

/**
 * Low-level PDF document builder.
 * Manages the object graph, cross-reference table, and final assembly.
 */
class PdfDocument
{
    /** @var array<int, string> */
    private array $objects = [];
    private int $objectCount = 0;
    /** @var list<int> */
    private array $pages = [];
    private int $pagesObjId;
    private int $catalogObjId;

    public function __construct()
    {
        $this->catalogObjId = $this->allocateObject();
        $this->pagesObjId = $this->allocateObject();
    }

    public function allocateObject(): int
    {
        $this->objectCount++;
        return $this->objectCount;
    }

    public function setObject(int $id, string $content): void
    {
        $this->objects[$id] = $content;
    }

    public function addPage(int $pageObjId): void
    {
        $this->pages[] = $pageObjId;
    }

    public function output(): string
    {
        $this->setObject($this->catalogObjId, "<< /Type /Catalog /Pages {$this->pagesObjId} 0 R >>");

        $kids = implode(' ', array_map(fn($id) => "{$id} 0 R", $this->pages));
        $count = count($this->pages);
        $this->setObject($this->pagesObjId, "<< /Type /Pages /Kids [{$kids}] /Count {$count} >>");

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [];

        for ($i = 1; $i <= $this->objectCount; $i++) {
            $offsets[$i] = strlen($pdf);
            $content = $this->objects[$i] ?? '<< >>';
            $pdf .= "{$i} 0 obj\n{$content}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n";
        $pdf .= "0 " . ($this->objectCount + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= $this->objectCount; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n";
        $pdf .= "<< /Size " . ($this->objectCount + 1) . " /Root {$this->catalogObjId} 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF\n";

        return $pdf;
    }

    public function getPagesObjId(): int
    {
        return $this->pagesObjId;
    }
}
