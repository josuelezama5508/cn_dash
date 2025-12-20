<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class ReporteControllerService
{
    private array $headers = [];
    private array $rows = [];

    /* =======================
       SETTERS / GETTERS
    ======================= */

    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    public function setRows(array $rows): self
    {
        $this->rows = $rows;
        return $this;
    }

    /* =======================
       EXCEL
    ======================= */

    public function generateExcel(
        array $headers,
        array $rows,
        string $filename = 'reporte.xlsx'
    ): void {
        $this->setHeaders($headers);
        $this->setRows($rows);

        if (empty($this->headers)) {
            throw new \Exception('No hay headers para generar el Excel.');
        }

        if (ob_get_length()) {
            ob_end_clean();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        /* -------- HEADERS -------- */
        $colIndex = 1;
        foreach ($this->headers as $header) {
            $cell = Coordinate::stringFromColumnIndex($colIndex) . '1';

            $sheet->setCellValueExplicit(
                $cell,
                (string) $header,
                DataType::TYPE_STRING
            );

            $sheet->getStyle($cell)
                ->getFont()
                ->setBold(true);

            $sheet->getColumnDimension(
                Coordinate::stringFromColumnIndex($colIndex)
            )->setAutoSize(true);

            $colIndex++;
        }

        /* -------- ROWS -------- */
        $rowIndex = 2;
        foreach ($this->rows as $row) {
            $colIndex = 1;

            foreach ($row as $value) {
                $cell = Coordinate::stringFromColumnIndex($colIndex) . $rowIndex;

                $sheet->setCellValueExplicit(
                    $cell,
                    (string) $value,
                    DataType::TYPE_STRING
                );

                $colIndex++;
            }

            $rowIndex++;
        }

        /* -------- OUTPUT -------- */
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
