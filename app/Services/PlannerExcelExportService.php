<?php

namespace App\Services;

use App\Models\EngagementTask;
use App\Models\Expense;
use App\Models\Gift;
use App\Models\Guest;
use App\Models\Vendor;
use Illuminate\Cache\FileStore;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PlannerExcelExportService
{
    private const LOCAL_DB_SHEET = '_db_local';
    private const LOCAL_DB_PASSWORD = 'planner_local_db';
    private const EXPORT_MEMORY_LIMIT = '128M';
    private const EXPORT_TIMEOUT_SECONDS = 180;
    private const MIN_VALIDATION_ROWS = 120;
    private const MAX_VALIDATION_ROWS = 5000;
    private const EXCEL_MAX_ROWS = 1048576;
    private const STREAM_CHUNK_BYTES = 1048576;
    private const CELL_CACHE_ROOT = 'app/phpspreadsheet-cell-cache';
    private const MEMORY_TRACE_FILE = 'app/planner-export-memory.log';

    private ?string $cellCachePath = null;
    private ?bool $liteMode = null;
    private ?int $validationRowCap = null;
    private array $memoryTimeline = [];
    private bool $shutdownHookRegistered = false;

    public function download(): StreamedResponse
    {
        $this->markMemory('download:start');
        $filename = 'wedding-planner-export-' . now()->format('Ymd_His') . '.xlsx';
        $spreadsheet = null;
        $tempFile = null;

        try {
            $this->prepareRuntimeForExport();
            $this->markMemory('download:runtime-ready');

            $spreadsheet = $this->buildSpreadsheet();
            $this->markMemory('download:spreadsheet-ready');
            $writer = new Xlsx($spreadsheet);
            $this->configureWriter($writer);
            $this->markMemory('download:writer-ready');

            $tempFile = $this->makeTempExportPath();
            $this->markMemory('download:before-save-temp', ['path' => $tempFile]);
            $writer->save($tempFile);
            $this->markMemory('download:after-save-temp', [
                'path' => $tempFile,
                'size_bytes' => @filesize($tempFile) ?: 0,
            ]);
        } catch (\Throwable $e) {
            if ($tempFile !== null && is_file($tempFile)) {
                @unlink($tempFile);
            }

            throw $e;
        } finally {
            if ($spreadsheet instanceof Spreadsheet) {
                $spreadsheet->disconnectWorksheets();
            }
            $this->cleanupCellCache();
            $this->markMemory('download:cleanup-done');
        }

        return response()->streamDownload(function () use ($tempFile) {
            $this->streamTempFileAndDelete($tempFile);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
        ]);
    }

    private function prepareRuntimeForExport(): void
    {
        $this->registerShutdownHook();
        @ini_set('memory_limit', self::EXPORT_MEMORY_LIMIT);
        if (function_exists('set_time_limit')) {
            @set_time_limit(self::EXPORT_TIMEOUT_SECONDS);
        }

        $this->configureCellCache();
        $this->markMemory('runtime:cache-configured', [
            'cell_cache_path' => $this->cellCachePath,
            'lite_mode' => $this->isLiteMode(),
        ]);
    }

    private function configureWriter(Xlsx $writer): void
    {
        $writer->setPreCalculateFormulas(false);
        $cacheDir = storage_path('app/phpspreadsheet-cache');
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0775, true);
        }
        if (is_dir($cacheDir) && is_writable($cacheDir)) {
            $writer->setUseDiskCaching(true, $cacheDir);
        }
    }

    private function configureCellCache(): void
    {
        try {
            $filesystem = new Filesystem();
            $root = storage_path(self::CELL_CACHE_ROOT);
            if (!is_dir($root)) {
                @mkdir($root, 0775, true);
            }

            $cachePath = $root . DIRECTORY_SEPARATOR . uniqid('cells_', true);
            if (!is_dir($cachePath)) {
                @mkdir($cachePath, 0775, true);
            }

            if (!is_dir($cachePath) || !is_writable($cachePath)) {
                return;
            }

            $store = new FileStore($filesystem, $cachePath, 0775);
            $repository = new CacheRepository($store);
            Settings::setCache($repository);
            $this->cellCachePath = $cachePath;
        } catch (\Throwable $e) {
            // Fallback ke memory cache default jika file cache tidak tersedia.
        }
    }

    private function cleanupCellCache(): void
    {
        Settings::setCache(null);
        if ($this->cellCachePath === null) {
            return;
        }

        try {
            $filesystem = new Filesystem();
            if ($filesystem->isDirectory($this->cellCachePath)) {
                $filesystem->deleteDirectory($this->cellCachePath);
            }
        } catch (\Throwable $e) {
            // Best effort cleanup.
        } finally {
            $this->cellCachePath = null;
        }
    }

    private function registerShutdownHook(): void
    {
        if ($this->shutdownHookRegistered) {
            return;
        }

        $this->shutdownHookRegistered = true;
        register_shutdown_function(function () {
            $error = error_get_last();
            if (!$error) {
                return;
            }

            $message = (string) ($error['message'] ?? '');
            if (stripos($message, 'Allowed memory size') === false) {
                return;
            }

            $payload = [
                'error' => $error,
                'timeline' => $this->memoryTimeline,
            ];
            Log::error('Planner Excel export OOM', [
                'error' => $error,
                'timeline' => $this->memoryTimeline,
            ]);
            $this->appendMemoryTrace([
                'label' => 'oom-shutdown',
                'payload' => $payload,
            ]);
        });
    }

    private function markMemory(string $label, array $context = []): void
    {
        $entry = array_merge([
            'label' => $label,
            'memory_limit' => (string) ini_get('memory_limit'),
            'usage_mb' => round(memory_get_usage(true) / 1048576, 2),
            'peak_mb' => round(memory_get_peak_usage(true) / 1048576, 2),
        ], $context);

        $this->memoryTimeline[] = $entry;
        Log::info('Planner Excel export memory', $entry);
        $this->appendMemoryTrace($entry);
    }

    private function appendMemoryTrace(array $entry): void
    {
        try {
            $path = storage_path(self::MEMORY_TRACE_FILE);
            $dir = dirname($path);
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }

            $line = sprintf(
                "[%s] %s\n",
                now()->format('Y-m-d H:i:s'),
                json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            );
            @file_put_contents($path, $line, FILE_APPEND);
        } catch (\Throwable $e) {
            // Best effort tracing only.
        }
    }

    private function makeTempExportPath(): string
    {
        $dir = storage_path('app/tmp');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        try {
            $suffix = bin2hex(random_bytes(10));
        } catch (\Throwable $e) {
            $suffix = uniqid('planner_', true);
        }

        return $dir . DIRECTORY_SEPARATOR . 'planner-export-' . $suffix . '.xlsx';
    }

    private function streamTempFileAndDelete(?string $path): void
    {
        if ($path === null || !is_file($path)) {
            return;
        }

        $handle = @fopen($path, 'rb');
        if ($handle === false) {
            @unlink($path);
            return;
        }

        try {
            while (!feof($handle)) {
                $chunk = fread($handle, self::STREAM_CHUNK_BYTES);
                if ($chunk === false) {
                    break;
                }
                echo $chunk;
                if (function_exists('flush')) {
                    @flush();
                }
            }
        } finally {
            @fclose($handle);
            @unlink($path);
        }
    }

    private function buildSpreadsheet(): Spreadsheet
    {
        $this->markMemory('build:start');
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('Wedding Planner')
            ->setTitle('Wedding Planner Export')
            ->setDescription('Export data planner dengan lookup DB di protected sheet');
        $this->markMemory('build:spreadsheet-created');

        $guestCount = (int) Guest::query()->count();
        $this->markMemory('build:guests-counted', ['count' => $guestCount]);
        $guestsSheet = $spreadsheet->getActiveSheet();
        $guestsSheet->setTitle('Undangan');
        $this->buildGuestsSheet($guestsSheet, Guest::query()
            ->select(['name', 'side', 'event_type', 'phone', 'attendance_status', 'notes'])
            ->orderBy('event_type')
            ->orderBy('side')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->cursor());
        $this->markMemory('build:guests-sheet-built');
        $this->markMemory('build:guests-cursor-drained');

        $taskCount = (int) EngagementTask::query()->count();
        $this->markMemory('build:tasks-counted', ['count' => $taskCount]);
        $tasksSheet = new Worksheet($spreadsheet, 'To-Do');
        $spreadsheet->addSheet($tasksSheet);
        $this->buildTasksSheet($tasksSheet, EngagementTask::query()
            ->select([
                'title',
                'vendor',
                'price',
                'paid_amount',
                'down_payment',
                'task_status',
                'start_date',
                'due_date',
                'finish_date',
                'notes',
            ])
            ->orderByRaw("task_status = 'done'")
            ->orderBy('due_date')
            ->orderBy('start_date')
            ->orderByDesc('created_at')
            ->cursor());
        $this->markMemory('build:tasks-sheet-built');
        $this->markMemory('build:tasks-cursor-drained');

        $giftCount = (int) Gift::query()->count();
        $this->markMemory('build:gifts-counted', ['count' => $giftCount]);
        $giftsSheet = new Worksheet($spreadsheet, 'Seserahan');
        $spreadsheet->addSheet($giftsSheet);
        $this->buildGiftsSheet($giftsSheet, Gift::query()
            ->select([
                'name',
                'brand',
                'group_name',
                'price',
                'paid_amount',
                'down_payment',
                'link',
                'status',
                'notes',
            ])
            ->orderBy('group_sort_order')
            ->orderByRaw("COALESCE(group_name, '')")
            ->orderBy('sort_order')
            ->orderBy('id')
            ->cursor());
        $this->markMemory('build:gifts-sheet-built');
        $this->markMemory('build:gifts-cursor-drained');

        $vendorCount = (int) Vendor::query()->count();
        $this->markMemory('build:vendors-counted', ['count' => $vendorCount]);
        $vendorLookup = $this->buildVendorLookup();
        $this->markMemory('build:vendor-lookup-ready', ['lookup_count' => count($vendorLookup)]);
        $vendorsSheet = new Worksheet($spreadsheet, 'Vendor');
        $spreadsheet->addSheet($vendorsSheet);
        $this->buildVendorsSheet($vendorsSheet, Vendor::query()
            ->select([
                'vendor_name',
                'contact_name',
                'contact_number',
                'contact_email',
                'website',
                'reference',
                'status',
            ])
            ->orderByRaw("status = 'done'")
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->cursor());
        $this->markMemory('build:vendors-sheet-built');
        $this->markMemory('build:vendors-cursor-drained');

        $expenseCount = (int) Expense::query()->count();
        $this->markMemory('build:expenses-counted', ['count' => $expenseCount]);
        $expensesSheet = new Worksheet($spreadsheet, 'BudgetExpense');
        $spreadsheet->addSheet($expensesSheet);
        $this->buildExpensesSheet($expensesSheet, Expense::query()
            ->select([
                'name',
                'category',
                'type',
                'amount',
                'entry_mode',
                'source_type',
                'source_id',
                'base_price',
                'paid_amount',
                'down_payment',
                'remaining_amount',
                'notes',
            ])
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->cursor());
        $this->markMemory('build:expenses-sheet-built');
        $this->markMemory('build:expenses-cursor-drained');

        $localSheet = new Worksheet($spreadsheet, self::LOCAL_DB_SHEET);
        $spreadsheet->addSheet($localSheet);
        $this->buildProtectedLocalDbSheet(
            $spreadsheet,
            $localSheet,
            $vendorLookup,
            $guestCount,
            $taskCount,
            $giftCount,
            $vendorCount,
            $expenseCount
        );
        $this->markMemory('build:local-sheet-built');
        unset($vendorLookup);
        $this->markMemory('build:lookup-released');

        $spreadsheet->setActiveSheetIndexByName('Undangan');
        $this->markMemory('build:completed');

        return $spreadsheet;
    }

    private function buildGuestsSheet(Worksheet $sheet, iterable $guests): void
    {
        $headers = ['Nama', 'Pihak', 'Event', 'Kontak', 'Status Kehadiran', 'Catatan'];
        $dataCount = $this->writeRowsSequentially($sheet, $headers, function () use ($guests) {
            foreach ($guests as $guest) {
                yield [
                (string) ($guest->name ?? ''),
                (string) ($guest->side ?? ''),
                (string) ($guest->event_type ?? ''),
                $this->formatPhoneDisplayId($guest->phone),
                (string) ($guest->attendance_status ?? ''),
                (string) ($guest->notes ?? ''),
                ];
            }
        });

        $maxRows = $this->resolveValidationEndRow($dataCount);
        if (!$this->isLiteMode()) {
            $this->setColumnWidths($sheet, [
                'A' => 28,
                'B' => 12,
                'C' => 14,
                'D' => 24,
                'E' => 20,
                'F' => 36,
            ]);
            $sheet->getStyle("D2:D{$maxRows}")
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_TEXT);
            $this->applyListValidation($sheet, 'E', 2, $maxRows, '=LookupGuestStatus');
        }
    }

    private function buildTasksSheet(Worksheet $sheet, iterable $tasks): void
    {
        $headers = [
            'Task',
            'Vendor',
            'Harga Awal',
            'Harga Final',
            'Sudah Dibayar',
            'Sisa Pelunasan',
            'Status',
            'Start Date',
            'Due Date',
            'Finish Date',
            'Notes',
        ];
        $dataCount = $this->writeRowsSequentially($sheet, $headers, function () use ($tasks) {
            foreach ($tasks as $task) {
                yield [
                (string) ($task->title ?? ''),
                (string) ($task->vendor ?? ''),
                $this->asNumber($task->price),
                $this->asNumber($task->paid_amount),
                $this->asNumber($task->down_payment),
                null,
                (string) ($task->task_status ?? ''),
                $this->asDateString($task->start_date),
                $this->asDateString($task->due_date),
                $this->asDateString($task->finish_date),
                (string) ($task->notes ?? ''),
                ];
            }
        });

        $maxRows = $this->resolveValidationEndRow($dataCount);
        if (!$this->isLiteMode()) {
            $this->setColumnWidths($sheet, [
                'A' => 36,
                'B' => 24,
                'C' => 16,
                'D' => 16,
                'E' => 16,
                'F' => 18,
                'G' => 14,
                'H' => 14,
                'I' => 14,
                'J' => 14,
                'K' => 36,
            ]);
            $sheet->getStyle("C2:F{$maxRows}")
                ->getNumberFormat()
                ->setFormatCode('#,##0');
            $sheet->getStyle("H2:J{$maxRows}")
                ->getNumberFormat()
                ->setFormatCode('yyyy-mm-dd');
            for ($row = 2; $row <= $dataCount + 1; $row++) {
                $sheet->setCellValue("F{$row}", "=IF(OR(D{$row}=\"\",E{$row}=\"\"),\"\",MAX(D{$row}-E{$row},0))");
            }
            $this->applyListValidation($sheet, 'B', 2, $maxRows, '=LookupVendors');
            $this->applyListValidation($sheet, 'G', 2, $maxRows, '=LookupTaskStatus');
        }
    }

    private function buildGiftsSheet(Worksheet $sheet, iterable $gifts): void
    {
        $headers = [
            'Nama',
            'Brand',
            'Kategori',
            'Harga Awal',
            'Harga Final',
            'Sudah Dibayar',
            'Link',
            'Status',
            'Notes',
        ];
        $dataCount = $this->writeRowsSequentially($sheet, $headers, function () use ($gifts) {
            foreach ($gifts as $gift) {
                yield [
                (string) ($gift->name ?? ''),
                (string) ($gift->brand ?? ''),
                (string) ($gift->group_name ?? ''),
                $this->asNumber($gift->price),
                $this->asNumber($gift->paid_amount),
                $this->asNumber($gift->down_payment),
                (string) ($gift->link ?? ''),
                (string) ($gift->status ?? ''),
                (string) ($gift->notes ?? ''),
                ];
            }
        });

        $maxRows = $this->resolveValidationEndRow($dataCount);
        if (!$this->isLiteMode()) {
            $this->setColumnWidths($sheet, [
                'A' => 28,
                'B' => 20,
                'C' => 18,
                'D' => 16,
                'E' => 16,
                'F' => 16,
                'G' => 40,
                'H' => 14,
                'I' => 30,
            ]);
            $sheet->getStyle("D2:F{$maxRows}")
                ->getNumberFormat()
                ->setFormatCode('#,##0');
            $this->applyListValidation($sheet, 'H', 2, $maxRows, '=LookupGiftStatus');
        }
    }

    private function buildVendorsSheet(Worksheet $sheet, iterable $vendors): void
    {
        $headers = [
            'Vendor Name',
            'Contact Name',
            'Contact Number',
            'Contact Email',
            'Website',
            'Reference',
            'Status',
        ];
        $dataCount = $this->writeRowsSequentially($sheet, $headers, function () use ($vendors) {
            foreach ($vendors as $vendor) {
                yield [
                (string) ($vendor->vendor_name ?? ''),
                (string) ($vendor->contact_name ?? ''),
                $this->formatPhoneDisplayId($vendor->contact_number),
                (string) ($vendor->contact_email ?? ''),
                (string) ($vendor->website ?? ''),
                (string) ($vendor->reference ?? ''),
                (string) ($vendor->status ?? ''),
                ];
            }
        });

        $maxRows = $this->resolveValidationEndRow($dataCount);
        if (!$this->isLiteMode()) {
            $this->setColumnWidths($sheet, [
                'A' => 28,
                'B' => 24,
                'C' => 24,
                'D' => 28,
                'E' => 36,
                'F' => 24,
                'G' => 16,
            ]);
            $sheet->getStyle("C2:C{$maxRows}")
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_TEXT);
            $this->applyListValidation($sheet, 'G', 2, $maxRows, '=LookupVendorStatus');
        }
    }

    private function buildExpensesSheet(Worksheet $sheet, iterable $expenses): void
    {
        $headers = [
            'Nama',
            'Kategori',
            'Type',
            'Amount',
            'Entry Mode',
            'Source Type',
            'Source ID',
            'Base Price',
            'Paid Amount',
            'Down Payment',
            'Remaining',
            'Notes',
        ];
        $dataCount = $this->writeRowsSequentially($sheet, $headers, function () use ($expenses) {
            foreach ($expenses as $expense) {
                yield [
                (string) ($expense->name ?? ''),
                (string) ($expense->category ?? ''),
                (string) ($expense->type ?? ''),
                $this->asNumber($expense->amount),
                (string) ($expense->entry_mode ?? ''),
                (string) ($expense->source_type ?? ''),
                $expense->source_id === null ? '' : (int) $expense->source_id,
                $this->asNumber($expense->base_price),
                $this->asNumber($expense->paid_amount),
                $this->asNumber($expense->down_payment),
                $this->asNumber($expense->remaining_amount),
                (string) ($expense->notes ?? ''),
                ];
            }
        });

        $maxRows = $this->resolveValidationEndRow($dataCount);
        if (!$this->isLiteMode()) {
            $this->setColumnWidths($sheet, [
                'A' => 30,
                'B' => 18,
                'C' => 12,
                'D' => 14,
                'E' => 14,
                'F' => 14,
                'G' => 10,
                'H' => 14,
                'I' => 14,
                'J' => 14,
                'K' => 14,
                'L' => 34,
            ]);
            $sheet->getStyle("D2:D{$maxRows}")
                ->getNumberFormat()
                ->setFormatCode('#,##0');
            $sheet->getStyle("H2:K{$maxRows}")
                ->getNumberFormat()
                ->setFormatCode('#,##0');
            $this->applyListValidation($sheet, 'C', 2, $maxRows, '=LookupExpenseType');
        }
    }

    private function buildProtectedLocalDbSheet(
        Spreadsheet $spreadsheet,
        Worksheet $sheet,
        array $vendorLookup,
        int $guestCount,
        int $taskCount,
        int $giftCount,
        int $vendorCount,
        int $expenseCount
    ): void {
        $this->writeLookupColumn($spreadsheet, $sheet, 'A', 'Vendors', $vendorLookup, 'LookupVendors');
        $this->writeLookupColumn($spreadsheet, $sheet, 'B', 'Guest Status', ['invited', 'attending', 'not_attending'], 'LookupGuestStatus');
        $this->writeLookupColumn($spreadsheet, $sheet, 'C', 'Task Status', ['not_started', 'in_progress', 'done'], 'LookupTaskStatus');
        $this->writeLookupColumn($spreadsheet, $sheet, 'D', 'Gift Status', ['not_started', 'on_delivery', 'complete'], 'LookupGiftStatus');
        $this->writeLookupColumn($spreadsheet, $sheet, 'E', 'Vendor Status', ['not_started', 'in_progress', 'done'], 'LookupVendorStatus');
        $this->writeLookupColumn($spreadsheet, $sheet, 'F', 'Expense Type', ['budget', 'expense'], 'LookupExpenseType');

        $sheet->setCellValue('H1', 'Local DB Snapshot');
        $sheet->setCellValue('H2', 'Generated At');
        $sheet->setCellValue('I2', now()->toDateTimeString());
        $sheet->setCellValue('H3', 'Guests Count');
        $sheet->setCellValue('I3', $guestCount);
        $sheet->setCellValue('H4', 'Tasks Count');
        $sheet->setCellValue('I4', $taskCount);
        $sheet->setCellValue('H5', 'Gifts Count');
        $sheet->setCellValue('I5', $giftCount);
        $sheet->setCellValue('H6', 'Vendors Count');
        $sheet->setCellValue('I6', $vendorCount);
        $sheet->setCellValue('H7', 'Expenses Count');
        $sheet->setCellValue('I7', $expenseCount);
        $sheet->getStyle('H1:I1')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'EFEFEF'],
            ],
        ]);
        $sheet->getStyle('H2:H7')->getFont()->setBold(true);

        $sheet->getProtection()->setPassword(self::LOCAL_DB_PASSWORD);
        $sheet->getProtection()->setSheet(true);
        $sheet->setSheetState(Worksheet::SHEETSTATE_VERYHIDDEN);
    }

    private function writeRowsSequentially(Worksheet $sheet, array $headers, callable $rowGenerator): int
    {
        $sheet->fromArray($headers, null, 'A1');
        $rowIndex = 2;
        $truncated = false;
        foreach ($rowGenerator() as $rowData) {
            if ($rowIndex > self::EXCEL_MAX_ROWS) {
                $truncated = true;
                break;
            }
            $sheet->fromArray([$rowData], null, 'A' . $rowIndex);
            $rowIndex++;
        }

        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
        $dataCount = $rowIndex - 2;
        $lastRow = max($dataCount + 1, 1);
        if (!$this->isLiteMode()) {
            $sheet->freezePane('A2');
            $sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");

            $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                'font' => [
                    'bold' => true,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'F1E6D8',
                    ],
                ],
            ]);
        }

        $this->markMemory('sheet:rows-written', [
            'sheet' => $sheet->getTitle(),
            'row_count' => $dataCount,
            'header_count' => count($headers),
            'truncated' => $truncated,
        ]);

        return $dataCount;
    }

    private function writeLookupColumn(
        Spreadsheet $spreadsheet,
        Worksheet $sheet,
        string $column,
        string $title,
        array $values,
        string $namedRange
    ): void {
        $normalized = [];
        foreach ($values as $value) {
            $val = trim((string) $value);
            if ($val === '') {
                continue;
            }
            $this->appendUniqueLookupValue($normalized, $val);
        }

        if (empty($normalized)) {
            $normalized[] = '';
        }

        $sheet->setCellValue("{$column}1", $title);
        $row = 2;
        foreach ($normalized as $value) {
            $sheet->setCellValueExplicit("{$column}{$row}", (string) $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $row++;
        }

        $endRow = max($row - 1, 2);
        $sheet->getStyle("{$column}1")->getFont()->setBold(true);
        $spreadsheet->addNamedRange(new NamedRange($namedRange, $sheet, '$' . $column . '$2:$' . $column . '$' . $endRow));
    }

    private function applyListValidation(Worksheet $sheet, string $column, int $startRow, int $endRow, string $formula): void
    {
        $endRow = min($endRow, $this->getValidationRowCap(), self::EXCEL_MAX_ROWS);
        if ($endRow < $startRow) {
            return;
        }

        $this->markMemory('validation:apply', [
            'sheet' => $sheet->getTitle(),
            'column' => $column,
            'start_row' => $startRow,
            'end_row' => $endRow,
            'formula' => $formula,
        ]);
        $validation = $sheet->getCell($column . $startRow)->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Nilai tidak valid');
        $validation->setError('Silakan pilih nilai dari daftar.');
        $validation->setFormula1($formula);
        $validation->setSqref($column . $startRow . ':' . $column . $endRow);
    }

    private function setColumnWidths(Worksheet $sheet, array $widths): void
    {
        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth((float) $width);
        }
    }

    private function resolveValidationEndRow(int $dataCount): int
    {
        $targetRow = max($dataCount + 60, self::MIN_VALIDATION_ROWS);

        return min($targetRow, $this->getValidationRowCap(), self::EXCEL_MAX_ROWS);
    }

    private function getValidationRowCap(): int
    {
        if ($this->validationRowCap !== null) {
            return $this->validationRowCap;
        }

        $raw = env('PLANNER_EXPORT_MAX_VALIDATION_ROWS', self::MAX_VALIDATION_ROWS);
        $parsed = is_numeric($raw) ? (int) $raw : self::MAX_VALIDATION_ROWS;
        $this->validationRowCap = max(self::MIN_VALIDATION_ROWS, min($parsed, self::EXCEL_MAX_ROWS));

        return $this->validationRowCap;
    }

    private function isLiteMode(): bool
    {
        if ($this->liteMode !== null) {
            return $this->liteMode;
        }

        $raw = env('PLANNER_EXPORT_LITE', false);
        if (is_bool($raw)) {
            $this->liteMode = $raw;
        } else {
            $this->liteMode = filter_var((string) $raw, FILTER_VALIDATE_BOOLEAN);
        }

        return $this->liteMode;
    }

    private function buildVendorLookup(): array
    {
        $lookup = [];

        $taskVendors = EngagementTask::query()
            ->whereNotNull('vendor')
            ->distinct()
            ->pluck('vendor');
        foreach ($taskVendors as $name) {
            $this->appendUniqueLookupValue($lookup, $name);
        }

        $vendorNames = Vendor::query()
            ->whereNotNull('vendor_name')
            ->distinct()
            ->pluck('vendor_name');
        foreach ($vendorNames as $name) {
            $this->appendUniqueLookupValue($lookup, $name);
        }

        natcasesort($lookup);

        return array_values($lookup);
    }

    private function appendUniqueLookupValue(array &$list, $value): void
    {
        $trimmed = trim((string) ($value ?? ''));
        if ($trimmed === '') {
            return;
        }

        foreach ($list as $existing) {
            if (strcasecmp((string) $existing, $trimmed) === 0) {
                return;
            }
        }

        $list[] = $trimmed;
    }

    private function asDateString($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return (string) $value;
    }

    private function asNumber($value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return (float) $value;
    }

    private function formatPhoneDisplayId($value): string
    {
        $digits = preg_replace('/\D+/', '', (string) ($value ?? ''));
        if ($digits === null || $digits === '') {
            return '';
        }

        $normalized = $digits;
        if (strpos($normalized, '0') === 0) {
            $normalized = '62' . substr($normalized, 1);
        } elseif (strpos($normalized, '8') === 0) {
            $normalized = '62' . $normalized;
        } elseif (strpos($normalized, '62') !== 0) {
            return '+' . $normalized;
        }

        $local = substr($normalized, 2);
        if ($local === '' || $local === false) {
            return '+62';
        }

        $parts = [];
        $length = strlen($local);
        if ($length <= 3) {
            $parts[] = $local;
        } else {
            $parts[] = substr($local, 0, 3);
            for ($offset = 3; $offset < $length; $offset += 4) {
                $parts[] = substr($local, $offset, 4);
            }
        }

        return '+62 ' . implode('-', $parts);
    }
}
