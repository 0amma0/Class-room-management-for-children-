<?php

namespace App\Support;

use RuntimeException;
use ZipArchive;

class XlsxWorkbook
{
    /**
     * @param  list<array{name: string, rows: list<list<int|float|string|null>>}>  $sheets
     */
    public function create(array $sheets): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('The ZIP extension is required to create Excel workbooks.');
        }
        if ($sheets === []) {
            throw new RuntimeException('An Excel workbook requires at least one worksheet.');
        }

        $path = tempnam(sys_get_temp_dir(), 'child-classes-');
        if ($path === false) {
            throw new RuntimeException('Unable to create a temporary Excel workbook.');
        }

        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
            @unlink($path);
            throw new RuntimeException('Unable to open the temporary Excel workbook.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes(count($sheets)));
        $zip->addFromString('_rels/.rels', $this->packageRelationships());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml($sheets));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationships(count($sheets)));

        foreach ($sheets as $index => $sheet) {
            $zip->addFromString('xl/worksheets/sheet'.($index + 1).'.xml', $this->worksheetXml($sheet['rows']));
        }

        if (! $zip->close()) {
            @unlink($path);
            throw new RuntimeException('Unable to finish the Excel workbook.');
        }

        return $path;
    }

    /**
     * @return list<array{row: int, values: array<int, string>}>
     */
    public function readFirstSheet(string $path): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('The ZIP extension is required to read Excel workbooks.');
        }

        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new RuntimeException('The uploaded Excel file is not a valid XLSX workbook.');
        }

        try {
            $sheetPath = $this->firstSheetPath($zip);
            $sheetContent = $zip->getFromName($sheetPath);
            if (! is_string($sheetContent)) {
                throw new RuntimeException('The first worksheet could not be read.');
            }

            $sheet = $this->parseXml($sheetContent, 'worksheet');
            $sharedStrings = [];
            $sharedContent = $zip->getFromName('xl/sharedStrings.xml');
            if (is_string($sharedContent)) {
                $sharedXml = $this->parseXml($sharedContent, 'shared strings');
                $sharedXml->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                foreach ($sharedXml->xpath('//main:si') ?: [] as $item) {
                    $item->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                    $texts = $item->xpath('.//main:t') ?: [];
                    $sharedStrings[] = implode('', array_map('strval', $texts));
                }
            }

            $sheet->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $parsedRows = [];
            foreach ($sheet->xpath('//main:sheetData/main:row') ?: [] as $row) {
                $row->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                $rowNumber = (int) $row['r'];
                $values = [];
                foreach ($row->xpath('./main:c') ?: [] as $cell) {
                    $cell->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                    if (isset($cell->f)) {
                        throw new RuntimeException("Formula cells are not accepted (row {$rowNumber}).");
                    }

                    $reference = (string) $cell['r'];
                    preg_match('/^[A-Z]+/', $reference, $matches);
                    $column = isset($matches[0]) ? $this->columnNumber($matches[0]) : 0;
                    if ($column === 0) {
                        continue;
                    }

                    $type = (string) $cell['t'];
                    if ($type === 'inlineStr') {
                        $texts = $cell->xpath('./main:is//main:t') ?: [];
                        $value = implode('', array_map('strval', $texts));
                    } else {
                        $value = isset($cell->v) ? (string) $cell->v : '';
                        if ($type === 's') {
                            $value = $sharedStrings[(int) $value] ?? '';
                        } elseif ($type === 'b') {
                            $value = $value === '1' ? '1' : '0';
                        }
                    }
                    $values[$column] = $value;
                }
                if ($values !== []) {
                    ksort($values);
                    $parsedRows[] = ['row' => $rowNumber, 'values' => $values];
                }
            }

            return $parsedRows;
        } finally {
            $zip->close();
        }
    }

    /**
     * @param  list<list<int|float|string|null>>  $rows
     */
    private function worksheetXml(array $rows): string
    {
        $xmlRows = '';
        foreach ($rows as $rowIndex => $row) {
            $rowNumber = $rowIndex + 1;
            $cells = '';
            foreach (array_values($row) as $columnIndex => $value) {
                $reference = $this->columnName($columnIndex + 1).$rowNumber;
                if (is_int($value) || is_float($value)) {
                    $cells .= '<c r="'.$reference.'" t="n"><v>'.$value.'</v></c>';
                } else {
                    $escaped = htmlspecialchars((string) ($value ?? ''), ENT_XML1 | ENT_QUOTES, 'UTF-8');
                    $cells .= '<c r="'.$reference.'" t="inlineStr"><is><t xml:space="preserve">'.$escaped.'</t></is></c>';
                }
            }
            $xmlRows .= '<row r="'.$rowNumber.'">'.$cells.'</row>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'.$xmlRows.'</sheetData></worksheet>';
    }

    /**
     * @param  list<array{name: string, rows: list<list<int|float|string|null>>}>  $sheets
     */
    private function workbookXml(array $sheets): string
    {
        $entries = '';
        foreach ($sheets as $index => $sheet) {
            $name = htmlspecialchars(substr($sheet['name'], 0, 31), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $entries .= '<sheet name="'.$name.'" sheetId="'.($index + 1).'" r:id="rId'.($index + 1).'"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>'.$entries.'</sheets></workbook>';
    }

    private function contentTypes(int $sheetCount): string
    {
        $overrides = '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';
        for ($index = 1; $index <= $sheetCount; $index++) {
            $overrides .= '<Override PartName="/xl/worksheets/sheet'.$index.'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/>'.$overrides.'</Types>';
    }

    private function packageRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
    }

    private function workbookRelationships(int $sheetCount): string
    {
        $entries = '';
        for ($index = 1; $index <= $sheetCount; $index++) {
            $entries .= '<Relationship Id="rId'.$index.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet'.$index.'.xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.$entries.'</Relationships>';
    }

    private function firstSheetPath(ZipArchive $zip): string
    {
        $workbookContent = $zip->getFromName('xl/workbook.xml');
        $relationshipsContent = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if (! is_string($workbookContent) || ! is_string($relationshipsContent)) {
            throw new RuntimeException('The uploaded Excel workbook is missing its workbook metadata.');
        }

        $workbook = $this->parseXml($workbookContent, 'workbook');
        $workbook->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $workbook->registerXPathNamespace('rel', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        $sheets = $workbook->xpath('//main:sheets/main:sheet') ?: [];
        if ($sheets === []) {
            throw new RuntimeException('The uploaded workbook does not contain a worksheet.');
        }
        $attributes = $sheets[0]->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        $relationshipId = (string) ($attributes['id'] ?? '');

        $relationships = $this->parseXml($relationshipsContent, 'workbook relationships');
        $relationships->registerXPathNamespace('rel', 'http://schemas.openxmlformats.org/package/2006/relationships');
        foreach ($relationships->xpath('//rel:Relationship') ?: [] as $relationship) {
            if ((string) $relationship['Id'] !== $relationshipId) {
                continue;
            }
            $target = ltrim((string) $relationship['Target'], '/');
            if (str_contains($target, '..')) {
                throw new RuntimeException('The uploaded workbook contains an invalid worksheet path.');
            }

            return str_starts_with($target, 'xl/') ? $target : 'xl/'.$target;
        }

        throw new RuntimeException('The first worksheet reference is invalid.');
    }

    private function parseXml(string $content, string $label): \SimpleXMLElement
    {
        $xml = simplexml_load_string($content, \SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOBLANKS);
        if ($xml === false) {
            throw new RuntimeException("The Excel {$label} XML is invalid.");
        }

        return $xml;
    }

    private function columnNumber(string $letters): int
    {
        $number = 0;
        foreach (str_split($letters) as $letter) {
            $number = ($number * 26) + ord($letter) - 64;
        }

        return $number;
    }

    private function columnName(int $number): string
    {
        $name = '';
        while ($number > 0) {
            $number--;
            $name = chr(($number % 26) + 65).$name;
            $number = intdiv($number, 26);
        }

        return $name;
    }
}
