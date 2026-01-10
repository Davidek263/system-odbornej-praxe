<?php

namespace App\Exports;

use App\Models\Internship;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GuarantorInternshipsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithCustomCsvSettings
{
    private array $filters;
    private array $columns;
    private array $stats;

    private ?Collection $cached = null;

    public function __construct(array $filters = [], array $columns = [], array $stats = [])
    {
        $this->filters = $filters;
        $this->columns = $columns;
        $this->stats   = $stats;
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',     // ✅ SK Excel
            'enclosure' => '"',
            'line_ending' => PHP_EOL,
            'use_bom' => true,      // ✅ diakritika
        ];
    }

    private function getData(): Collection
    {
        if ($this->cached) return $this->cached;

        $query = Internship::with([
            'student.studyField',
            'company',
            'currentStatus',
        ]);

        // ------- FILTRE (z FE) -------
        if (!empty($this->filters['academic_year'])) {
            $query->where('academic_year', $this->filters['academic_year']);
        }

        if (!empty($this->filters['status'])) {
            $statusName = $this->filters['status'];
            $query->whereHas('currentStatus', function ($q) use ($statusName) {
                $q->where('internship_status_name', $statusName);
            });
        }

        if (!empty($this->filters['company'])) {
            $companyName = $this->filters['company'];
            $query->whereHas('company', function ($q) use ($companyName) {
                $q->where('company_name', $companyName);
            });
        }

        if (!empty($this->filters['study_field'])) {
            $fieldName = $this->filters['study_field'];
            $query->whereHas('student.studyField', function ($q) use ($fieldName) {
                $q->where('study_field_name', $fieldName);
            });
        }

        if (!empty($this->filters['student'])) {
            $needle = mb_strtolower($this->filters['student']);
            $query->whereHas('student', function ($q) use ($needle) {
                $q->whereRaw('LOWER(first_name) LIKE ?', ["%{$needle}%"])
                  ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$needle}%"]);
            });
        }

        if (!empty($this->filters['search'])) {
            $s = mb_strtolower($this->filters['search']);
            $query->where(function ($q) use ($s) {
                $q->whereHas('student', function ($qs) use ($s) {
                    $qs->whereRaw('LOWER(first_name) LIKE ?', ["%{$s}%"])
                       ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$s}%"])
                       ->orWhereRaw('LOWER(email) LIKE ?', ["%{$s}%"])
                       ->orWhereRaw('LOWER(student_email) LIKE ?', ["%{$s}%"]);
                })
                ->orWhereHas('company', function ($qc) use ($s) {
                    $qc->whereRaw('LOWER(company_name) LIKE ?', ["%{$s}%"]);
                })
                ->orWhereHas('student.studyField', function ($qf) use ($s) {
                    $qf->whereRaw('LOWER(study_field_name) LIKE ?', ["%{$s}%"])
                       ->orWhereRaw('LOWER(abbreviation) LIKE ?', ["%{$s}%"]);
                })
                ->orWhereRaw('LOWER(academic_year) LIKE ?', ["%{$s}%"])
                ->orWhereHas('currentStatus', function ($qst) use ($s) {
                    $qst->whereRaw('LOWER(internship_status_name) LIKE ?', ["%{$s}%"]);
                });
            });
        }

        $this->cached = $query->orderBy('created_at', 'desc')->get();
        return $this->cached;
    }

    private function columnMap(): array
    {
        return [
            'student' => [
                'label' => 'Študent',
                'value' => function ($i) {
                    $fn = $i->student->first_name ?? '';
                    $ln = $i->student->last_name ?? '';
                    $email = $i->student->student_email ?? $i->student->email ?? '';
                    $name = trim($fn . ' ' . $ln);
                    return $email ? "{$name} ({$email})" : $name;
                },
            ],
            'company' => [
                'label' => 'Firma',
                'value' => fn($i) => $i->company->company_name ?? '—',
            ],
            'studyField' => [
                'label' => 'Študijný odbor',
                'value' => fn($i) => $i->student->studyField->study_field_name ?? '—',
            ],
            'academicYear' => [
                'label' => 'Akademický rok',
                'value' => fn($i) => $i->academic_year ?? '—',
            ],
            'dateRange' => [
                'label' => 'Termín praxe',
                'value' => function ($i) {
                    $start = $i->date_start ?? '';
                    $end   = $i->date_end ?? '';
                    return trim($start . ' - ' . $end);
                },
            ],
            'status' => [
                'label' => 'Stav praxe',
                'value' => fn($i) => $i->currentStatus->internship_status_name ?? '—',
            ],
        ];
    }

    public function headings(): array
    {
        $map = $this->columnMap();

        return collect($this->columns)
            ->filter(fn($c) => isset($map[$c]))
            ->map(fn($c) => $map[$c]['label'])
            ->values()
            ->all();
    }

    public function collection(): Collection
    {
        return $this->getData();
    }

    public function map($internship): array
    {
        $map = $this->columnMap();

        return collect($this->columns)
            ->filter(fn($c) => isset($map[$c]))
            ->map(fn($c) => ($map[$c]['value'])($internship))
            ->values()
            ->all();
    }
}
