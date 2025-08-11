<?php

namespace App\Exports;

use App\Models\ProductInventory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class InventoryExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = ProductInventory::with([
            'productVariant.product',
            'storeLocation.province',
            'storeLocation.district'
        ]);

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->whereHas('productVariant', function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhereHas('product', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        if (!empty($this->filters['province_code'])) {
            $query->whereHas('storeLocation', fn($q) => $q->where('province_code', $this->filters['province_code']));
        }

        if (!empty($this->filters['district_code'])) {
            $query->whereHas('storeLocation', fn($q) => $q->where('district_code', $this->filters['district_code']));
        }

        if (!empty($this->filters['location_type']) && $this->filters['location_type'] !== 'all') {
            $query->whereHas('storeLocation', fn($q) => $q->where('type', $this->filters['location_type']));
        }

        $items = $query->get();

        return $items->map(function ($item) {
            $availableQty = max(0, $item->quantity - ($item->held_quantity ?? 0));
            $costPrice = $item->productVariant->cost_price ?? 0;

            return [
                'SKU' => $item->productVariant->sku ?? '',
                'Tên sản phẩm' => $item->productVariant->product->name ?? '',
                'Địa điểm' => $item->storeLocation->name ?? '',
                'Loại kho' => $item->storeLocation->type ?? '',
                'Tồn vật lý' => $item->quantity,
                'Tạm giữ' => $item->held_quantity ?? 0,
                'Tồn khả dụng' => $availableQty,
                'Giá vốn' => $costPrice,
                'Giá trị tồn' => $costPrice * $availableQty,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'SKU',
            'Tên sản phẩm',
            'Địa điểm',
            'Loại kho',
            'Tồn vật lý',
            'Tạm giữ',
            'Tồn khả dụng',
            'Giá vốn',
            'Giá trị tồn'
        ];
    }

    // Định dạng style cho header và data
    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        return [
            // Header bold, background xanh nhạt
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9EAD3'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
            // Căn giữa dữ liệu cho cột text và số
            'A2:I' . $highestRow => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    // Sự kiện sau khi sheet được tạo để chỉnh auto width, format số
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // Auto size cột A đến I
                foreach (range('A', 'I') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Định dạng số cho các cột số và tiền tệ
                $sheet->getStyle("E2:G{$highestRow}")
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);

                $sheet->getStyle("H2:I{$highestRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0_-"₫"'); // định dạng tiền Việt

            }
        ];
    }
}
