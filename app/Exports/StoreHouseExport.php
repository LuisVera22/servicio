<?php

namespace App\Exports;

use App\Models\StoreHouse;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StoreHouseExport implements WithStyles, WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function styles(Worksheet $sheet): array
    {
        // Agregar logo y título
        $drawing = new Drawing();
        $drawing->setPath(public_path('storage/img_logo_empresa/spektro.png'))
            ->setName('SPEKTRO_LOGO')
            ->setDescription('Logo de Spektro')
            ->setCoordinates('A1')
            ->setHeight(60)
            ->setOffsetY(-5)
            ->setOffsetX(10)
            ->getShadow()
            ->setVisible(true)
            ->setDirection(45);

        $drawing->setWorksheet($sheet);

        // Estilo para la primera fila (encabezado del logo y título)
        $sheet->getStyle('A1:F3')->applyFromArray([
            'font' => [
                "color" => ["rgb" => "000"],
                "bold" => true,
                'size' => 20,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'f5f5f5'],
            ],
        ])->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $sheet->mergeCells('A1:F3');
        $sheet->setCellValue('A1', 'Listado de Productos Para Almacenar');



        // Estilo para los encabezados de la lista
        $sheet->getStyle('A5:C5')->applyFromArray([
            'font' => [
                "color" => ["rgb" => "FFFFFF"],
                "bold" => true,
                'size' => 11,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4682B4'],
            ],
        ])->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);


        // Estilo para el encabezado
        $sheet->setCellValue('A5', 'CÓDIGO')
            ->setCellValue('B5', 'PRODUCTO')
            ->setCellValue('C5', 'CANTIDAD PARA ALMACENAR');

        // Establecer el ancho de las columnas
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(100);
        $sheet->getColumnDimension('C')->setWidth(30);

        $spreadsheet = new Spreadsheet($sheet);
        $spreadsheet->getDefaultStyle()
            ->getFont()
            ->setName('Arial')
            ->setSize(11);

        $sheet->setTitle("Lista para Almacén");

        return [];
    }

    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $id = $this->id;

                $storehouses = StoreHouse::select('*')
                    ->whereHas('products.typemanufacturing', function ($query) use ($id) {
                        $query->where('id', $id);
                    })
                    ->where([
                        ['enabled', '1'],
                    ])
                    ->get();

                $storehouses->makeHidden(['idcombination', 'idproduct']);

                $row = 6;

                foreach ($storehouses as $storehouse) {

                    $sheet->setCellValue('A' . $row, $storehouse->codigo)
                        ->setCellValue('B' . $row, $storehouse->product)
                        ->setCellValueExplicit('C' . $row, 0, DataType::TYPE_NUMERIC);

                    $sheet->getStyle("A" . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $sheet->getStyle("C" . $row)->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);

                    if ($row % 2 != 0) {
                        $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'f5f5f5'],
                            ],
                        ]);
                    } else {
                        $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'dadada'],
                            ],
                        ]);
                    }
                    $row++;
                }

                $sheet->getStyle("A6:A" . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("A6:C" . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                $sheet->setAutoFilter('A5:B5')->getStyle('A5:B5')->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);

                $protection = $sheet->getProtection();
                $protection->setSheet(true); // Activar la protección de la hoja
                $protection->setSort(true);  // Permitir ordenar
                $protection->setAutoFilter(false); // Permitir usar filtros automáticos
                $protection->setInsertRows(true); // Prohibir la inserción de filas
                $protection->setDeleteRows(true); // Prohibir la eliminación de filas
                $protection->setInsertColumns(true); // Prohibir la inserción de columnas
                $protection->setDeleteColumns(true); // Prohibir la eliminación de columnas
                $protection->setFormatCells(true); // Permitir la modificación de formato de celdas
                $protection->setFormatColumns(true); // Permitir la modificación del formato de columnas
                $protection->setFormatRows(true); // Permitir la modificación del formato de filas
            }
        ];
    }
}
