<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\SettlementLineItem;
use App\Models\PartnerSettlementPeriod;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Class xuất báo cáo bảng kê chi tiết đối soát sang file Excel.
 */
class SettlementLineItemsExport implements FromQuery, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected int $periodId;
    protected int $rowCount = 0;

    public function __construct(int $periodId)
    {
        $this->periodId = $periodId;
    }

    /**
     * Query truy vấn dữ liệu line items.
     */
    public function query()
    {
        return SettlementLineItem::query()
            ->where('settlement_period_id', $this->periodId)
            ->with(['booking.room.property']);
    }

    /**
     * Định nghĩa các tiêu đề cột.
     */
    public function headings(): array
    {
        return [
            'STT',
            'Mã Đơn Đặt Phòng',
            'Tên Phòng / Căn Hộ',
            'Tên Thuộc Tính',
            'Ngày Check-out Thực Tế',
            'Doanh Thu Phòng (VND)',
            'Doanh Thu Dịch Vụ (VND)',
            'Tổng GMV Đơn (VND)',
            'Phí Hoa Hồng (VND)',
        ];
    }

    /**
     * Ánh xạ từng dòng dữ liệu.
     */
    public function map($row): array
    {
        $this->rowCount++;

        $roomTitle = $row->booking?->room?->title ?? 'N/A';
        $propertyTitle = $row->booking?->room?->property?->name ?? 'N/A';

        return [
            $this->rowCount,
            $row->booking_code,
            $roomTitle,
            $propertyTitle,
            $row->checkout_date ? $row->checkout_date->format('d/m/Y') : 'N/A',
            $row->room_gmv,
            $row->services_gmv,
            $row->total_gmv,
            $row->commission_amount,
        ];
    }

    /**
     * Tên của Sheet trong Excel.
     */
    public function title(): string
    {
        return 'Bảng kê đối soát chi tiết';
    }

    /**
     * Định dạng style cho file Excel thêm phần premium chuyên nghiệp.
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            // Tiêu đề in đậm, căn giữa, background xanh emerald
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '10B981']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ]
            ],
        ];
    }
}
