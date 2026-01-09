<?php

namespace App\Exports;

use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ShiftReportExport implements FromArray, WithHeadings, WithStyles
{
    protected $startDate;
    protected $endDate;
    protected $days;
    protected $dailyTotals = [];
    protected $dailyTotalsCost = [];
    protected $grandTotalCost = 0;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = Carbon::parse($startDate);
        $this->endDate   = Carbon::parse($endDate);
        $this->days      = $this->startDate->daysUntil($this->endDate->copy()->addDay())->toArray();
    }

    public function array(): array
    {
        $users = User::with(['shifts' => function ($q) {
            $q->whereBetween('date', [$this->startDate, $this->endDate]);
        }])->get();

        $rows = [];

        foreach ($users as $user) {
            $row = [$user->name];
            $totalMinutes = 0;
            $totalOvertime = 0;
            $totalCost = 0;

            $rate = $user->rate ?? 0;
            $overtimeRate = $user->overtime_rate ?? $rate;

            foreach ($this->days as $day) {
                $dayStr = $day->format('Y-m-d');
                $entries = $user->shifts->where('date', $dayStr);

                if ($entries->count()) {
                    $cellContent = "";
                    $dayCost = 0;

                    foreach ($entries as $shift) {
                        
                        $start = null;
                        $end = null;
                        $minutes = 0; 
                        if (!empty($shift->shift_time) && str_contains($shift->shift_time, '-')) {
                            [$startStr, $endStr] = explode('-', $shift->shift_time);

                            $start = Carbon::parse($shift->date . ' ' . trim($startStr));
                            $end = Carbon::parse($shift->date . ' ' . trim($endStr));

                            // Handle overnight shifts (e.g. 8PM–7AM)
                            if ($end->lt($start)) {
                                $end->addDay();
                            }

                            $minutes = $end->diffInMinutes($start);
                        } elseif ($shift->shift_type === 'LD') {
                            $start = Carbon::parse($shift->date . ' 07:15');
                            $end   = Carbon::parse($shift->date . ' 20:30');
                            $minutes = $end->diffInMinutes($start);
                        } elseif ($shift->shift_type === 'N') {
                            $start = Carbon::parse($shift->date . ' 20:15');
                            $end   = Carbon::parse($shift->date)->addDay()->setTime(7, 30);
                            $minutes = $end->diffInMinutes($start);
                        } else {
                            $minutes = 0;
                        }

                        // overtime calc
                        $otHours = $shift->overtime_hours ?? 0;
                        $otMinutes = $shift->overtime_minutes ?? 0;
                        $otTotalMinutes = ($otHours * 60) + $otMinutes;

                        $normalHours = $minutes / 60;
                        $overtimeHours = $otTotalMinutes / 60;

                        $cost = round(($normalHours * $rate) + ($overtimeHours * $overtimeRate), 2);

                        $totalMinutes += $minutes;
                        $totalOvertime += $otTotalMinutes;
                        $totalCost += $cost;
                        $dayCost += $cost;

                        $this->dailyTotals[$dayStr] = ($this->dailyTotals[$dayStr] ?? 0) + $minutes + $otTotalMinutes;
                        $this->dailyTotalsCost[$dayStr] = ($this->dailyTotalsCost[$dayStr] ?? 0) + $cost;
                        $this->grandTotalCost += $cost;

                        $cellContent .= $shift->shift_type . "\n";
                        $cellContent .= floor($minutes / 60) . "h " . ($minutes % 60) . "m\n";
                        if ($otTotalMinutes > 0) {
                            $cellContent .= "OT: " . floor($otTotalMinutes / 60) . "h " . ($otTotalMinutes % 60) . "m\n";
                        }
                        $cellContent .= "£" . $cost . "\n";
                    }

                    $row[] = trim($cellContent);
                } else {
                    $row[] = "-";
                }
            }

            $row[] = floor($totalMinutes / 60) . "h " . ($totalMinutes % 60) . "m";
            $row[] = $totalOvertime > 0
                ? floor($totalOvertime / 60) . "h " . ($totalOvertime % 60) . "m"
                : "-";
            $row[] = "£" . $rate;
            $row[] = "£" . $overtimeRate;
            $row[] = "£" . number_format($totalCost, 2);

            $rows[] = $row;
        }

        // footer totals
        $footer = ["Total (All Employees)"];
        foreach ($this->days as $day) {
            $minutes = $this->dailyTotals[$day->format('Y-m-d')] ?? 0;
            $footer[] = $minutes
                ? floor($minutes / 60) . "h " . ($minutes % 60) . "m"
                : "-";
        }

        $footer[] = floor(array_sum($this->dailyTotals) / 60) . "h " . (array_sum($this->dailyTotals) % 60) . "m";
        $footer[] = "-";
        $footer[] = "-";
        $footer[] = "-";
        $footer[] = "£" . number_format($this->grandTotalCost, 2);
        $rows[] = $footer;

        return $rows;
    }

    public function headings(): array
    {
        $headings = ["Employee"];
        foreach ($this->days as $day) {
            $headings[] = $day->format('d-m-y');
        }
        $headings[] = "Total Hours";
        $headings[] = "Total Overtime";
        $headings[] = "Rate P/H (£)";
        $headings[] = "OT Rate (£)";
        $headings[] = "Total Cost";
        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getColumnDimension('A')->setWidth(25);
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

        for ($col = 2; $col <= $highestColumnIndex; $col++) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($colLetter)->setWidth(18);
        }

        $sheet->getStyle('A1:' . $highestColumn . '1')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . $highestColumn . '1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF000000');
        $sheet->getStyle('A1:' . $highestColumn . '1')->getFont()->getColor()->setARGB('FFFFFFFF');

        $sheet->getStyle('A2:' . $highestColumn . $sheet->getHighestRow())
            ->getAlignment()->setWrapText(true);

        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A{$lastRow}:" . $highestColumn . "{$lastRow}")
            ->getFont()->setBold(true);
        $sheet->getStyle("A{$lastRow}:" . $highestColumn . "{$lastRow}")
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFDDDDDD');
    }
}
