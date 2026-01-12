<?php

namespace App\Exports;

/**
 * ===================================
 * IMPORTS
 * ===================================
 */
use App\Models\Internship;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * ===================================
 * CLASS: GuarantorInternshipsExport
 * ===================================
 *
 * Export class for generating CSV files of internship data for guarantors.
 *
 * This class handles the export of internship records with advanced filtering
 * and customizable column selection. It implements multiple Laravel Excel concerns
 * to provide:
 * - Dynamic column selection based on frontend requirements
 * - Advanced filtering (academic year, status, company, study field, student name, search)
 * - CSV settings optimized for Slovak Excel (semicolon delimiter, UTF-8 BOM for diacritics)
 * - Auto-sized columns for better readability
 * - Custom data mapping for each exportable field
 *
 * The export supports various filters passed from the frontend and caches the query
 * results to avoid duplicate database calls when generating headings and data rows.
 *
 * @package App\Exports
 * @author System
 */
class GuarantorInternshipsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithCustomCsvSettings
{
    /**
     * ===================================
     * PROPERTIES
     * ===================================
     */

    /**
     * Filters applied to the internship query
     *
     * @var array
     */
    private array $filters;

    /**
     * Columns to include in the export
     *
     * @var array
     */
    private array $columns;

    /**
     * Statistics data (currently unused, reserved for future use)
     *
     * @var array
     */
    private array $stats;

    /**
     * Cached collection of internships to prevent duplicate queries
     *
     * @var Collection|null
     */
    private ?Collection $cached = null;

    /**
     * ===================================
     * CONSTRUCTOR
     * ===================================
     */

    /**
     * Create a new export instance.
     *
     * @param array $filters Filters to apply (academic_year, status, company, study_field, student, search)
     * @param array $columns Column keys to export (student, company, studyField, academicYear, dateRange, status)
     * @param array $stats Statistics data (reserved for future use)
     * @return void
     */
    public function __construct(array $filters = [], array $columns = [], array $stats = [])
    {
        $this->filters = $filters;
        $this->columns = $columns;
        $this->stats   = $stats;
    }

    /**
     * ===================================
     * CSV CONFIGURATION
     * ===================================
     */

    /**
     * Configure CSV settings for Slovak Excel compatibility.
     *
     * Uses semicolon delimiter (standard for SK/CZ Excel) and UTF-8 BOM
     * to ensure proper display of diacritics (á, č, ď, é, etc.).
     *
     * @return array
     */
    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',
            'enclosure' => '"',
            'line_ending' => PHP_EOL,
            'use_bom' => true,
        ];
    }

    /**
     * ===================================
     * DATA RETRIEVAL
     * ===================================
     */

    /**
     * Retrieve and cache internship data with applied filters.
     *
     * Builds a query with eager loading of related models (student, company, status)
     * and applies all filters from the frontend. Results are cached to avoid
     * duplicate queries when generating both headings and data rows.
     *
     * Supported filters:
     * - academic_year: Filter by specific academic year
     * - status: Filter by internship status name
     * - company: Filter by company name
     * - study_field: Filter by study field name
     * - student: Search in student first/last name
     * - search: Global search across all searchable fields
     *
     * @return Collection
     */
    private function getData(): Collection
    {
        if ($this->cached) return $this->cached;

        $query = Internship::with([
            'student.studyField',
            'company',
            'currentStatus',
        ]);

        // Filter by academic year
        if (!empty($this->filters['academic_year'])) {
            $query->where('academic_year', $this->filters['academic_year']);
        }

        // Filter by internship status
        if (!empty($this->filters['status'])) {
            $statusName = $this->filters['status'];
            $query->whereHas('currentStatus', function ($q) use ($statusName) {
                $q->where('internship_status_name', $statusName);
            });
        }

        // Filter by company name
        if (!empty($this->filters['company'])) {
            $companyName = $this->filters['company'];
            $query->whereHas('company', function ($q) use ($companyName) {
                $q->where('company_name', $companyName);
            });
        }

        // Filter by study field
        if (!empty($this->filters['study_field'])) {
            $fieldName = $this->filters['study_field'];
            $query->whereHas('student.studyField', function ($q) use ($fieldName) {
                $q->where('study_field_name', $fieldName);
            });
        }

        // Filter by student name
        if (!empty($this->filters['student'])) {
            $needle = mb_strtolower($this->filters['student']);
            $query->whereHas('student', function ($q) use ($needle) {
                $q->whereRaw('LOWER(first_name) LIKE ?', ["%{$needle}%"])
                  ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$needle}%"]);
            });
        }

        // Global search filter across multiple fields
        if (!empty($this->filters['search'])) {
            $s = mb_strtolower($this->filters['search']);
            $query->where(function ($q) use ($s) {
                // Search in student fields
                $q->whereHas('student', function ($qs) use ($s) {
                    $qs->whereRaw('LOWER(first_name) LIKE ?', ["%{$s}%"])
                       ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$s}%"])
                       ->orWhereRaw('LOWER(email) LIKE ?', ["%{$s}%"])
                       ->orWhereRaw('LOWER(student_email) LIKE ?', ["%{$s}%"]);
                })
                // Search in company name
                ->orWhereHas('company', function ($qc) use ($s) {
                    $qc->whereRaw('LOWER(company_name) LIKE ?', ["%{$s}%"]);
                })
                // Search in study field
                ->orWhereHas('student.studyField', function ($qf) use ($s) {
                    $qf->whereRaw('LOWER(study_field_name) LIKE ?', ["%{$s}%"])
                       ->orWhereRaw('LOWER(abbreviation) LIKE ?', ["%{$s}%"]);
                })
                // Search in academic year
                ->orWhereRaw('LOWER(academic_year) LIKE ?', ["%{$s}%"])
                // Search in status
                ->orWhereHas('currentStatus', function ($qst) use ($s) {
                    $qst->whereRaw('LOWER(internship_status_name) LIKE ?', ["%{$s}%"]);
                });
            });
        }

        // Cache and return results ordered by creation date
        $this->cached = $query->orderBy('created_at', 'desc')->get();
        return $this->cached;
    }

    /**
     * ===================================
     * COLUMN MAPPING
     * ===================================
     */

    /**
     * Define column mappings with labels and value extractors.
     *
     * Each column definition contains:
     * - label: The header text for the CSV column
     * - value: A closure that extracts/formats the value from an internship record
     *
     * Available columns:
     * - student: Student name with email
     * - company: Company name
     * - studyField: Study field name
     * - academicYear: Academic year
     * - dateRange: Internship date range (start - end)
     * - status: Current internship status
     *
     * @return array
     */
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

    /**
     * ===================================
     * LARAVEL EXCEL INTERFACE METHODS
     * ===================================
     */

    /**
     * Generate CSV column headings based on selected columns.
     *
     * Filters the column map to include only selected columns
     * and returns their labels in the correct order.
     *
     * @return array
     */
    public function headings(): array
    {
        $map = $this->columnMap();

        return collect($this->columns)
            ->filter(fn($c) => isset($map[$c]))
            ->map(fn($c) => $map[$c]['label'])
            ->values()
            ->all();
    }

    /**
     * Provide the collection of internships to export.
     *
     * @return Collection
     */
    public function collection(): Collection
    {
        return $this->getData();
    }

    /**
     * Map an internship record to CSV row values.
     *
     * Extracts values for selected columns using the value closures
     * defined in columnMap().
     *
     * @param mixed $internship The internship record to map
     * @return array
     */
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
