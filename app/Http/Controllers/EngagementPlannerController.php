<?php

namespace App\Http\Controllers;

use App\Models\EngagementTask;
use App\Models\Expense;
use App\Models\Gift;
use App\Models\Guest;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EngagementPlannerController extends Controller
{
    public function index()
    {
        return redirect()->route('guests.index');
    }

    public function guestsPage(): View
    {
        $guests = Guest::query()
            ->orderBy('event_type')
            ->orderBy('side')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $lamaranGuests = $guests->where('event_type', 'lamaran')->values();
        $resepsiGuests = $guests->where('event_type', 'resepsi')->values();
        $lamaranCppGuests = $lamaranGuests->where('side', 'cpp')->values();
        $lamaranCpwGuests = $lamaranGuests->where('side', 'cpw')->values();
        $resepsiCppGuests = $resepsiGuests->where('side', 'cpp')->values();
        $resepsiCpwGuests = $resepsiGuests->where('side', 'cpw')->values();

        return view('planner.guests', [
            'lamaranCppGuests' => $lamaranCppGuests,
            'lamaranCpwGuests' => $lamaranCpwGuests,
            'resepsiCppGuests' => $resepsiCppGuests,
            'resepsiCpwGuests' => $resepsiCpwGuests,
            'stats' => [
                'totalGuests' => $guests->count(),
                'attendingGuests' => $guests->where('attendance_status', 'attending')->count(),
                'notAttendingGuests' => $guests->where('attendance_status', 'not_attending')->count(),
                'cppTotalGuests' => $guests->where('side', 'cpp')->count(),
                'cpwTotalGuests' => $guests->where('side', 'cpw')->count(),
                'lamaran' => [
                    'totalGuests' => $lamaranGuests->count(),
                    'attendingGuests' => $lamaranGuests->where('attendance_status', 'attending')->count(),
                    'notAttendingGuests' => $lamaranGuests->where('attendance_status', 'not_attending')->count(),
                    'cppTotalGuests' => $lamaranCppGuests->count(),
                    'cpwTotalGuests' => $lamaranCpwGuests->count(),
                ],
                'resepsi' => [
                    'totalGuests' => $resepsiGuests->count(),
                    'attendingGuests' => $resepsiGuests->where('attendance_status', 'attending')->count(),
                    'notAttendingGuests' => $resepsiGuests->where('attendance_status', 'not_attending')->count(),
                    'cppTotalGuests' => $resepsiCppGuests->count(),
                    'cpwTotalGuests' => $resepsiCpwGuests->count(),
                ],
            ],
        ]);
    }

    public function tasksPage(): View
    {
        $tasks = EngagementTask::query()
            ->orderByRaw("task_status = 'done'")
            ->orderBy('due_date')
            ->orderBy('start_date')
            ->orderByDesc('created_at')
            ->get();
        $vendorNames = Vendor::query()
            ->select('vendor_name')
            ->distinct()
            ->orderBy('vendor_name')
            ->pluck('vendor_name');

        return view('planner.tasks', [
            'tasks' => $tasks,
            'vendorNames' => $vendorNames,
            'stats' => [
                'openTasks' => $tasks->whereIn('task_status', ['not_started', 'in_progress'])->count(),
                'doneTasks' => $tasks->where('task_status', 'done')->count(),
            ],
        ]);
    }

    public function vendorsPage(): View
    {
        $vendors = Vendor::query()
            ->orderBy('group_sort_order')
            ->orderByRaw("COALESCE(group_name, '')")
            ->orderBy('id')
            ->get();

        return view('planner.vendors', [
            'vendors' => $vendors,
            'stats' => [
                'totalVendors' => $vendors->count(),
                'activeVendors' => $vendors->whereIn('status', ['not_started', 'in_progress'])->count(),
                'doneVendors' => $vendors->where('status', 'done')->count(),
            ],
        ]);
    }

    public function giftsPage(): View
    {
        $gifts = Gift::query()
            ->orderBy('group_sort_order')
            ->orderByRaw("COALESCE(group_name, '')")
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        return view('planner.gifts', [
            'gifts' => $gifts,
            'totalGiftBudget' => (float) Gift::sum('price'),
            'totalGiftFinal' => (float) Gift::sum('paid_amount'),
        ]);
    }

    public function expensesPage(): View
    {
        $manualExpenses = Expense::query()
            ->where('entry_mode', 'manual')
            ->orderByDesc('created_at')
            ->get();
        $autoExpenses = Expense::query()
            ->where('entry_mode', 'auto')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();

        $manualBudget = (float) Expense::where('entry_mode', 'manual')->where('type', 'budget')->sum('amount');
        $manualExpense = (float) Expense::where('entry_mode', 'manual')->where('type', 'expense')->sum('amount');
        $autoBase = (float) Expense::where('entry_mode', 'auto')->sum('base_price');
        $autoPaid = (float) Expense::where('entry_mode', 'auto')->sum('down_payment');
        $autoRemaining = (float) Expense::where('entry_mode', 'auto')->sum('remaining_amount');
        $paidAutoExpenses = $autoExpenses->filter(function (Expense $expense) {
            return (float) $expense->paid_amount > 0;
        });
        $totalSavings = (float) $paidAutoExpenses->sum(function (Expense $expense) {
            return max((float) $expense->base_price - (float) $expense->paid_amount, 0);
        });
        $totalDebt = (float) $autoExpenses->sum(function (Expense $expense) {
            return max((float) $expense->remaining_amount, 0);
        });
        $paidAutoBase = (float) $paidAutoExpenses->sum(function (Expense $expense) {
            return max((float) $expense->base_price, 0);
        });
        $savingsPercentage = $paidAutoBase > 0 ? ($totalSavings / $paidAutoBase) * 100 : 0.0;

        $totalBudget = $manualBudget;
        $totalExpense = $manualExpense + $autoPaid;

        return view('planner.expenses', [
            'manualExpenses' => $manualExpenses,
            'autoExpenses' => $autoExpenses,
            'stats' => [
                'totalBudget' => $totalBudget,
                'totalExpense' => $totalExpense,
                'remainingBudget' => $totalBudget - $totalExpense,
                'manualBudget' => $manualBudget,
                'manualExpense' => $manualExpense,
                'autoBase' => $autoBase,
                'autoPaid' => $autoPaid,
                'autoRemaining' => $autoRemaining,
                'totalSavings' => $totalSavings,
                'totalDebt' => $totalDebt,
                'savingsPercentage' => $savingsPercentage,
            ],
        ]);
    }

    public function storeGuest(Request $request)
    {
        $this->setClientId($request);

        $validated = $request->validate($this->guestRules());

        $validated['sort_order'] = $this->nextGuestSortOrder($validated['event_type'], $validated['side']);

        $guest = Guest::create($validated);

        return $this->respondSuccess($request, 'Undangan berhasil ditambahkan.', ['record' => $guest]);
    }

    public function updateGuest(Request $request, Guest $guest)
    {
        $this->setClientId($request);

        $validated = $request->validate($this->guestRules());

        $sideChanged = $validated['side'] !== $guest->side || $validated['event_type'] !== $guest->event_type;
        if ($sideChanged && !isset($validated['sort_order'])) {
            $validated['sort_order'] = $this->nextGuestSortOrder($validated['event_type'], $validated['side']);
        }

        $guest->update($validated);

        return $this->respondSuccess($request, 'Undangan diperbarui.', ['record' => $guest->fresh()]);
    }

    public function reorderGuests(Request $request)
    {
        $validated = $request->validate([
            'event_type' => ['required', 'in:lamaran,resepsi'],
            'side' => ['required', 'in:cpp,cpw'],
            'ordered_ids' => ['required', 'array'],
            'ordered_ids.*' => ['integer'],
        ]);

        $orderedIds = array_values(array_unique(array_map('intval', $validated['ordered_ids'])));
        if (empty($orderedIds)) {
            return response()->json(['message' => 'Urutan undangan diperbarui.']);
        }

        $existingCount = Guest::query()->whereIn('id', $orderedIds)->count();
        if ($existingCount !== count($orderedIds)) {
            return response()->json(['message' => 'Ada ID undangan yang tidak valid.'], 422);
        }

        DB::transaction(function () use ($validated, $request) {
            $this->setClientId($request);
            $orderedIds = array_values(array_unique(array_map('intval', $validated['ordered_ids'])));
            if (empty($orderedIds)) {
                return;
            }

            $eventType = $validated['event_type'];
            $side = $validated['side'];

            $currentRows = Guest::query()
                ->whereIn('id', $orderedIds)
                ->get(['id', 'event_type', 'side', 'sort_order'])
                ->keyBy('id');

            $changedIds = [];
            $sortCaseParts = [];
            $order = 1;

            foreach ($orderedIds as $id) {
                $row = $currentRows->get($id);
                if (!$row) {
                    $order++;
                    continue;
                }

                $needsChange = ((string) $row->event_type !== (string) $eventType)
                    || ((string) $row->side !== (string) $side)
                    || ((int) $row->sort_order !== $order);

                if ($needsChange) {
                    $changedIds[] = $id;
                    $sortCaseParts[] = 'WHEN ' . $id . ' THEN ' . $order;
                }
                $order++;
            }

            if (empty($changedIds)) {
                return;
            }

            $idList = implode(',', $changedIds);
            $sortCaseSql = implode(' ', $sortCaseParts);
            $eventTypeEscaped = str_replace("'", "''", (string) $eventType);
            $sideEscaped = str_replace("'", "''", (string) $side);

            DB::statement(
                "UPDATE guests SET event_type = '" . $eventTypeEscaped . "', side = '" . $sideEscaped . "', " .
                'sort_order = CASE id ' . $sortCaseSql . ' ELSE sort_order END ' .
                'WHERE id IN (' . $idList . ')'
            );
        });

        return response()->json(['message' => 'Urutan undangan diperbarui.']);
    }

    public function destroyGuest(Request $request, Guest $guest)
    {
        $this->setClientId($request);
        $guest->delete();

        return $this->respondSuccess($request, 'Undangan dihapus.');
    }

    public function storeGuestsBulk(Request $request)
    {
        $this->setClientId($request);
        $rows = $this->validateBulkRows($request, $this->guestRules());
        $records = [];

        DB::transaction(function () use (&$records, $rows) {
            $nextSortMap = [];
            foreach ($rows as $validated) {
                $pairKey = $validated['event_type'] . '|' . $validated['side'];
                if (!isset($nextSortMap[$pairKey])) {
                    $max = (int) Guest::query()
                        ->where('event_type', $validated['event_type'])
                        ->where('side', $validated['side'])
                        ->max('sort_order');
                    $nextSortMap[$pairKey] = $max + 1;
                }
                $validated['sort_order'] = $nextSortMap[$pairKey];
                $nextSortMap[$pairKey]++;
                $records[] = Guest::create($validated);
            }
        });

        return $this->respondSuccess($request, 'Undangan bulk berhasil ditambahkan.', ['records' => $records]);
    }

    public function destroyGuestsBulk(Request $request)
    {
        $this->setClientId($request);
        $ids = $this->validateBulkIds($request);
        if (empty($ids)) {
            return $this->respondSuccess($request, 'Tidak ada data undangan yang dihapus.', ['deleted_count' => 0]);
        }

        $deletedCount = Guest::query()->whereIn('id', $ids)->delete();

        return $this->respondSuccess($request, 'Undangan terpilih dihapus.', ['deleted_count' => $deletedCount]);
    }

    public function storeTask(Request $request)
    {
        $this->setClientId($request);

        $validated = $request->validate($this->taskRules());

        $task = DB::transaction(function () use ($validated) {
            $payload = $this->prepareTaskPayload($validated);
            $task = EngagementTask::create($payload);
            $this->syncAutoExpenseFromTask($task);

            return $task;
        });

        return $this->respondSuccess($request, 'Task berhasil ditambahkan.', ['record' => $task]);
    }

    public function updateTask(Request $request, EngagementTask $task)
    {
        $this->setClientId($request);

        $validated = $request->validate($this->taskRules());

        DB::transaction(function () use ($task, $validated) {
            $payload = $this->prepareTaskPayload($validated);
            $task->update($payload);
            $this->syncAutoExpenseFromTask($task->fresh());
        });

        return $this->respondSuccess($request, 'Task diperbarui.', ['record' => $task->fresh()]);
    }

    public function destroyTask(Request $request, EngagementTask $task)
    {
        $this->setClientId($request);
        DB::transaction(function () use ($task) {
            $this->deleteAutoExpense('task', (int) $task->id);
            $task->delete();
        });

        return $this->respondSuccess($request, 'Task dihapus.');
    }

    public function storeTasksBulk(Request $request)
    {
        $this->setClientId($request);
        $rows = $this->validateBulkRows($request, $this->taskRules());
        $records = [];

        DB::transaction(function () use (&$records, $rows) {
            foreach ($rows as $validated) {
                $payload = $this->prepareTaskPayload($validated);
                $task = EngagementTask::create($payload);
                $this->syncAutoExpenseFromTask($task);
                $records[] = $task;
            }
        });

        return $this->respondSuccess($request, 'Task bulk berhasil ditambahkan.', ['records' => $records]);
    }

    public function destroyTasksBulk(Request $request)
    {
        $this->setClientId($request);
        $ids = $this->validateBulkIds($request);
        if (empty($ids)) {
            return $this->respondSuccess($request, 'Tidak ada task yang dihapus.', ['deleted_count' => 0]);
        }

        $deletedCount = 0;
        DB::transaction(function () use ($ids, &$deletedCount) {
            Expense::query()->where('source_type', 'task')->whereIn('source_id', $ids)->delete();
            $deletedCount = EngagementTask::query()->whereIn('id', $ids)->delete();
        });

        return $this->respondSuccess($request, 'Task terpilih dihapus.', ['deleted_count' => $deletedCount]);
    }

    public function storeVendor(Request $request)
    {
        $this->setClientId($request);

        $validated = $this->normalizeVendorPayload($request->validate($this->vendorRules()));
        $vendor = DB::transaction(function () use ($validated) {
            return $this->upsertVendorByName($validated);
        });

        return $this->respondSuccess($request, 'Vendor berhasil ditambahkan.', ['record' => $vendor->fresh()]);
    }

    public function updateVendor(Request $request, Vendor $vendor)
    {
        $this->setClientId($request);

        $validated = $this->normalizeVendorPayload($request->validate($this->vendorRules()), $vendor);
        $duplicate = $this->findVendorByName($validated['vendor_name'] ?? null, (int) $vendor->id);
        if ($duplicate !== null) {
            $message = 'Nama vendor sudah digunakan vendor lain.';
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->withErrors(['vendor_name' => $message]);
        }

        try {
            $vendor->update($validated);
        } catch (QueryException $exception) {
            if ($this->isUniqueViolation($exception)) {
                $message = 'Nama vendor sudah digunakan vendor lain.';
                if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                    return response()->json(['message' => $message], 422);
                }

                return back()->withErrors(['vendor_name' => $message]);
            }
            throw $exception;
        }

        return $this->respondSuccess($request, 'Vendor diperbarui.', ['record' => $vendor->fresh()]);
    }

    public function destroyVendor(Request $request, Vendor $vendor)
    {
        $this->setClientId($request);
        $vendor->delete();

        return $this->respondSuccess($request, 'Vendor dihapus.');
    }

    public function storeVendorsBulk(Request $request)
    {
        $this->setClientId($request);
        $rows = $this->validateBulkRows($request, $this->vendorRules());
        $records = [];

        DB::transaction(function () use (&$records, $rows) {
            foreach ($rows as $validated) {
                $payload = $this->normalizeVendorPayload($validated);
                $records[] = $this->upsertVendorByName($payload);
            }
        });

        return $this->respondSuccess($request, 'Vendor bulk berhasil ditambahkan.', ['records' => $records]);
    }

    public function destroyVendorsBulk(Request $request)
    {
        $this->setClientId($request);
        $ids = $this->validateBulkIds($request);
        if (empty($ids)) {
            return $this->respondSuccess($request, 'Tidak ada vendor yang dihapus.', ['deleted_count' => 0]);
        }

        $deletedCount = Vendor::query()->whereIn('id', $ids)->delete();

        return $this->respondSuccess($request, 'Vendor terpilih dihapus.', ['deleted_count' => $deletedCount]);
    }

    public function reorderVendorGroups(Request $request)
    {
        $validated = $request->validate([
            'ordered_groups' => ['required', 'array'],
            'ordered_groups.*' => ['nullable', 'string', 'max:150'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $this->setClientId($request);
            $order = 1;

            foreach ($validated['ordered_groups'] as $groupNameRaw) {
                $groupName = $this->normalizeVendorGroupName($groupNameRaw);

                $query = Vendor::query();
                if ($groupName === null) {
                    $query->where(function ($q) {
                        $q->whereNull('group_name')->orWhere('group_name', '');
                    });
                } else {
                    $query->where('group_name', $groupName);
                }

                $query->update(['group_sort_order' => $order]);
                $order++;
            }
        });

        return response()->json(['message' => 'Urutan group vendor diperbarui.']);
    }

    public function storeGift(Request $request)
    {
        $validated = $request->validate($this->giftRules());
        $validated = $this->normalizeGiftPayload($validated);

        $gift = DB::transaction(function () use ($validated, $request) {
            $this->setClientId($request);
            $gift = Gift::create($validated);
            $this->syncAutoExpenseFromGift($gift);

            return $gift;
        });

        return $this->respondSuccess($request, 'Item seserahan berhasil ditambahkan.', ['record' => $gift]);
    }

    public function updateGift(Request $request, Gift $gift)
    {
        $validated = $request->validate($this->giftRules());
        $validated = $this->normalizeGiftPayload($validated, $gift);

        DB::transaction(function () use ($gift, $validated, $request) {
            $this->setClientId($request);
            $gift->update($validated);
            $this->syncAutoExpenseFromGift($gift->fresh());
        });

        return $this->respondSuccess($request, 'Status seserahan diperbarui.', ['record' => $gift->fresh()]);
    }

    public function reorderGifts(Request $request)
    {
        $validated = $request->validate([
            'ordered_ids' => ['required', 'array'],
            'ordered_ids.*' => ['integer'],
        ]);

        $orderedIds = array_values(array_unique(array_map('intval', $validated['ordered_ids'])));
        if (empty($orderedIds)) {
            return response()->json(['message' => 'Urutan seserahan diperbarui.']);
        }

        $existingCount = Gift::query()->whereIn('id', $orderedIds)->count();
        if ($existingCount !== count($orderedIds)) {
            return response()->json(['message' => 'Ada ID seserahan yang tidak valid.'], 422);
        }

        DB::transaction(function () use ($validated, $request) {
            $this->setClientId($request);
            $orderedIds = array_values(array_unique(array_map('intval', $validated['ordered_ids'])));
            if (empty($orderedIds)) {
                return;
            }

            $currentSortMap = Gift::query()
                ->whereIn('id', $orderedIds)
                ->pluck('sort_order', 'id');

            $changedMap = [];
            $order = 1;
            foreach ($orderedIds as $id) {
                $current = isset($currentSortMap[$id]) ? (int) $currentSortMap[$id] : null;
                if ($current !== $order) {
                    $changedMap[$id] = $order;
                }
                $order++;
            }

            if (empty($changedMap)) {
                return;
            }

            $caseParts = [];
            foreach ($changedMap as $id => $newOrder) {
                $caseParts[] = 'WHEN ' . $id . ' THEN ' . $newOrder;
            }

            $idList = implode(',', array_keys($changedMap));
            $caseSql = implode(' ', $caseParts);

            DB::statement(
                'UPDATE gifts SET sort_order = CASE id ' . $caseSql . ' ELSE sort_order END WHERE id IN (' . $idList . ')'
            );
        });

        return response()->json(['message' => 'Urutan seserahan diperbarui.']);
    }

    public function reorderGiftGroups(Request $request)
    {
        $validated = $request->validate([
            'ordered_groups' => ['required', 'array'],
            'ordered_groups.*' => ['nullable', 'string', 'max:150'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $this->setClientId($request);
            $order = 1;

            foreach ($validated['ordered_groups'] as $groupNameRaw) {
                $groupName = $this->normalizeGiftGroupName($groupNameRaw);

                $query = Gift::query();
                if ($groupName === null) {
                    $query->where(function ($q) {
                        $q->whereNull('group_name')->orWhere('group_name', '');
                    });
                } else {
                    $query->where('group_name', $groupName);
                }

                $query->update(['group_sort_order' => $order]);
                $order++;
            }
        });

        return response()->json(['message' => 'Urutan group seserahan diperbarui.']);
    }

    public function destroyGift(Request $request, Gift $gift)
    {
        $this->setClientId($request);
        DB::transaction(function () use ($gift) {
            $this->deleteAutoExpense('gift', (int) $gift->id);
            $gift->delete();
        });

        return $this->respondSuccess($request, 'Item seserahan dihapus.');
    }

    public function storeGiftsBulk(Request $request)
    {
        $this->setClientId($request);
        $rows = $this->validateBulkRows($request, $this->giftRules());
        $records = [];

        DB::transaction(function () use (&$records, $rows) {
            $nextSortOrder = (int) Gift::max('sort_order');
            $groupSortCache = [];
            foreach ($rows as $validated) {
                $payload = $validated;
                $payload['group_name'] = $this->normalizeGiftGroupName($payload['group_name'] ?? null);
                if (!isset($payload['sort_order']) || (int) $payload['sort_order'] <= 0) {
                    $nextSortOrder++;
                    $payload['sort_order'] = $nextSortOrder;
                }
                if (!isset($payload['group_sort_order']) || (int) $payload['group_sort_order'] <= 0) {
                    $groupKey = $payload['group_name'] ?? '__NULL__';
                    if (!isset($groupSortCache[$groupKey])) {
                        $groupSortCache[$groupKey] = $this->resolveGiftGroupSortOrder($payload['group_name']);
                    }
                    $payload['group_sort_order'] = $groupSortCache[$groupKey];
                }
                $gift = Gift::create($payload);
                $this->syncAutoExpenseFromGift($gift);
                $records[] = $gift;
            }
        });

        return $this->respondSuccess($request, 'Item seserahan bulk berhasil ditambahkan.', ['records' => $records]);
    }

    public function destroyGiftsBulk(Request $request)
    {
        $this->setClientId($request);
        $ids = $this->validateBulkIds($request);
        if (empty($ids)) {
            return $this->respondSuccess($request, 'Tidak ada item seserahan yang dihapus.', ['deleted_count' => 0]);
        }

        $deletedCount = 0;
        DB::transaction(function () use ($ids, &$deletedCount) {
            Expense::query()->where('source_type', 'gift')->whereIn('source_id', $ids)->delete();
            $deletedCount = Gift::query()->whereIn('id', $ids)->delete();
        });

        return $this->respondSuccess($request, 'Item seserahan terpilih dihapus.', ['deleted_count' => $deletedCount]);
    }

    public function storeExpense(Request $request)
    {
        $this->setClientId($request);

        $validated = $request->validate($this->expenseRules());
        $validated = $this->mergeManualExpenseBreakdown($validated);

        $expense = Expense::create($validated);

        return $this->respondSuccess($request, 'Catatan budget/expense ditambahkan.', ['record' => $expense]);
    }

    public function updateExpense(Request $request, Expense $expense)
    {
        $this->setClientId($request);

        if ($expense->entry_mode === 'auto') {
            return response()->json([
                'message' => 'Expense otomatis tidak bisa diubah manual. Ubah dari To-do atau Seserahan.',
            ], 422);
        }

        $validated = $request->validate($this->expenseRules());
        $validated = $this->mergeManualExpenseBreakdown($validated);

        $expense->update($validated);

        return $this->respondSuccess($request, 'Catatan budget/expense diperbarui.', ['record' => $expense->fresh()]);
    }

    public function destroyExpense(Request $request, Expense $expense)
    {
        if ($expense->entry_mode === 'auto') {
            return response()->json([
                'message' => 'Expense otomatis tidak bisa dihapus manual. Hapus dari To-do atau Seserahan.',
            ], 422);
        }

        $this->setClientId($request);
        $expense->delete();

        return $this->respondSuccess($request, 'Catatan budget/expense dihapus.');
    }

    public function storeExpensesBulk(Request $request)
    {
        $this->setClientId($request);
        $rows = $this->validateBulkRows($request, $this->expenseRules());
        $records = [];

        DB::transaction(function () use (&$records, $rows) {
            foreach ($rows as $validated) {
                $payload = $this->mergeManualExpenseBreakdown($validated);
                $records[] = Expense::create($payload);
            }
        });

        return $this->respondSuccess($request, 'Catatan budget/expense bulk berhasil ditambahkan.', ['records' => $records]);
    }

    public function destroyExpensesBulk(Request $request)
    {
        $this->setClientId($request);
        $ids = $this->validateBulkIds($request);
        if (empty($ids)) {
            return $this->respondSuccess($request, 'Tidak ada catatan budget/expense yang dihapus.', ['deleted_count' => 0]);
        }

        $deletedCount = Expense::query()
            ->whereIn('id', $ids)
            ->where('entry_mode', 'manual')
            ->delete();

        return $this->respondSuccess($request, 'Catatan budget/expense terpilih dihapus.', ['deleted_count' => $deletedCount]);
    }

    private function guestRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'side' => ['required', 'in:cpp,cpw'],
            'event_type' => ['required', 'in:lamaran,resepsi'],
            'attendance_status' => ['required', 'in:invited,attending,not_attending'],
            'phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    private function taskRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'vendor' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'down_payment' => ['nullable', 'numeric', 'min:0'],
            'task_status' => ['required', 'in:not_started,in_progress,done'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'finish_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    private function vendorRules(): array
    {
        return [
            'vendor_name' => ['required', 'string', 'max:255'],
            'group_name' => ['nullable', 'string', 'max:150'],
            'group_sort_order' => ['nullable', 'integer', 'min:0'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:80'],
            'contact_email' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:2048'],
            'reference' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:not_started,in_progress,done'],
        ];
    }

    private function giftRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'group_name' => ['nullable', 'string', 'max:150'],
            'group_sort_order' => ['nullable', 'integer', 'min:0'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'down_payment' => ['nullable', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'link' => ['nullable', 'string', 'max:2048'],
            'status' => ['required', 'in:not_started,on_delivery,complete'],
            'notes' => ['nullable', 'string'],
        ];
    }

    private function expenseRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'type' => ['required', 'in:budget,expense'],
            'amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }

    private function prepareTaskPayload(array $validated): array
    {
        $validated['vendor'] = $this->ensureTaskVendorExists($validated['vendor'] ?? null);
        $validated['price'] = $this->normalizeNonNegativeNumber($validated, 'price');
        $validated['paid_amount'] = $this->normalizeNonNegativeNumber($validated, 'paid_amount');
        $validated['down_payment'] = $this->normalizeNonNegativeNumber($validated, 'down_payment');

        $paid = $validated['paid_amount'];
        $dp = $validated['down_payment'];
        $validated['remaining_amount'] = max($paid - $dp, 0);
        $validated['status'] = $validated['task_status'] === 'done' ? 'done' : 'pending';

        return $validated;
    }

    private function ensureTaskVendorExists($value): ?string
    {
        $vendorName = $this->normalizeVendorName($value);
        if ($vendorName === null) {
            return null;
        }

        $vendor = $this->upsertVendorByName([
            'vendor_name' => $vendorName,
            'status' => 'not_started',
        ]);

        return $vendor->vendor_name;
    }

    private function upsertVendorByName(array $payload): Vendor
    {
        $payload = $this->normalizeVendorPayload($payload);
        $vendorName = $payload['vendor_name'] ?? '';
        if ($vendorName === '') {
            throw ValidationException::withMessages([
                'vendor_name' => 'Nama vendor wajib diisi.',
            ]);
        }

        $existing = $this->findVendorByName($vendorName);
        if ($existing !== null) {
            $merged = $this->mergeVendorPayload($existing, $payload);
            if (!empty($merged)) {
                $existing->fill($merged);
                if ($existing->isDirty()) {
                    $existing->save();
                }
            }

            return $existing->fresh();
        }

        try {
            return Vendor::create($payload);
        } catch (QueryException $exception) {
            if (!$this->isUniqueViolation($exception)) {
                throw $exception;
            }

            $existing = $this->findVendorByName($vendorName);
            if ($existing === null) {
                throw $exception;
            }

            $merged = $this->mergeVendorPayload($existing, $payload);
            if (!empty($merged)) {
                $existing->fill($merged);
                if ($existing->isDirty()) {
                    $existing->save();
                }
            }

            return $existing->fresh();
        }
    }

    private function findVendorByName($value, ?int $ignoreVendorId = null): ?Vendor
    {
        $vendorName = $this->normalizeVendorName($value);
        if ($vendorName === null) {
            return null;
        }

        $query = Vendor::query()
            ->whereRaw('LOWER(vendor_name) = ?', [strtolower($vendorName)]);
        if ($ignoreVendorId !== null && $ignoreVendorId > 0) {
            $query->where('id', '<>', $ignoreVendorId);
        }

        return $query->first();
    }

    private function mergeVendorPayload(Vendor $existing, array $incoming): array
    {
        $incomingStatus = $incoming['status'] ?? null;
        $incomingGroupName = $incoming['group_name'] ?? null;
        $incomingGroupSortOrder = isset($incoming['group_sort_order']) ? (int) $incoming['group_sort_order'] : 0;

        $groupName = $incomingGroupName ?? $this->normalizeVendorGroupName($existing->group_name);
        $groupSortOrder = (int) ($existing->group_sort_order ?? 0);
        if ($incomingGroupName !== null) {
            $groupSortOrder = $incomingGroupSortOrder > 0
                ? $incomingGroupSortOrder
                : $this->resolveVendorGroupSortOrder($incomingGroupName, (int) $existing->id);
        } elseif ($groupSortOrder <= 0) {
            $groupSortOrder = $this->resolveVendorGroupSortOrder($groupName, (int) $existing->id);
        }

        return [
            'vendor_name' => $incoming['vendor_name'] ?? $existing->vendor_name,
            'group_name' => $groupName,
            'group_sort_order' => $groupSortOrder,
            'contact_name' => $incoming['contact_name'] ?? $existing->contact_name,
            'contact_number' => $incoming['contact_number'] ?? $existing->contact_number,
            'contact_email' => $incoming['contact_email'] ?? $existing->contact_email,
            'website' => $incoming['website'] ?? $existing->website,
            'reference' => $incoming['reference'] ?? $existing->reference,
            'status' => $this->resolveVendorStatus($existing->status, $incomingStatus),
        ];
    }

    private function resolveVendorStatus(?string $current, ?string $incoming): string
    {
        $rank = [
            'not_started' => 1,
            'in_progress' => 2,
            'done' => 3,
        ];

        $currentStatus = $current ?? 'not_started';
        $incomingStatus = $incoming ?? $currentStatus;
        $currentRank = $rank[$currentStatus] ?? 0;
        $incomingRank = $rank[$incomingStatus] ?? 0;

        return $incomingRank >= $currentRank ? $incomingStatus : $currentStatus;
    }

    private function normalizeVendorPayload(array $payload, ?Vendor $vendor = null): array
    {
        $payload['vendor_name'] = $this->normalizeVendorName($payload['vendor_name'] ?? null) ?? '';
        $payload['group_name'] = $this->normalizeVendorGroupName($payload['group_name'] ?? null);
        $incomingGroupSortOrder = isset($payload['group_sort_order']) ? (int) $payload['group_sort_order'] : 0;
        $payload['contact_name'] = $this->normalizeVendorText($payload['contact_name'] ?? null);
        $payload['contact_number'] = $this->normalizeVendorPhoneDigits($payload['contact_number'] ?? null);
        $payload['contact_email'] = $this->normalizeVendorText($payload['contact_email'] ?? null);
        if ($payload['contact_email'] !== null) {
            $payload['contact_email'] = strtolower($payload['contact_email']);
        }
        $payload['website'] = $this->normalizeVendorText($payload['website'] ?? null);
        $payload['reference'] = $this->normalizeVendorText($payload['reference'] ?? null);
        $payload['status'] = $payload['status'] ?? 'not_started';
        if ($vendor === null) {
            if ($incomingGroupSortOrder <= 0) {
                $payload['group_sort_order'] = $this->resolveVendorGroupSortOrder($payload['group_name']);
            } else {
                $payload['group_sort_order'] = $incomingGroupSortOrder;
            }

            return $payload;
        }

        $groupChanged = $payload['group_name'] !== $this->normalizeVendorGroupName($vendor->group_name);
        if ($groupChanged) {
            $payload['group_sort_order'] = $this->resolveVendorGroupSortOrder($payload['group_name'], (int) $vendor->id);
        } elseif ($incomingGroupSortOrder <= 0) {
            $payload['group_sort_order'] = (int) $vendor->group_sort_order;
            if ($payload['group_sort_order'] <= 0) {
                $payload['group_sort_order'] = $this->resolveVendorGroupSortOrder($payload['group_name'], (int) $vendor->id);
            }
        } else {
            $payload['group_sort_order'] = $incomingGroupSortOrder;
        }

        return $payload;
    }

    private function normalizeVendorName($value): ?string
    {
        return $this->normalizeVendorText($value);
    }

    private function normalizeVendorGroupName($value): ?string
    {
        return $this->normalizeVendorText($value);
    }

    private function normalizeVendorText($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = preg_replace('/\s+/u', ' ', trim((string) $value));
        if ($normalized === null || $normalized === '') {
            return null;
        }

        return $normalized;
    }

    private function normalizeVendorPhoneDigits($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', (string) $value);
        if ($digits === null || $digits === '') {
            return null;
        }

        return $digits;
    }

    private function isUniqueViolation(QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? null;

        return $sqlState === '23505';
    }

    private function normalizeGiftPayload(array $validated, ?Gift $gift = null): array
    {
        $validated['price'] = $this->normalizeNonNegativeNumber($validated, 'price');
        $validated['paid_amount'] = $this->normalizeNonNegativeNumber($validated, 'paid_amount');
        // Seserahan tidak pakai DP parsial: nilai sudah dibayar mengikuti harga final.
        $validated['down_payment'] = $validated['paid_amount'];
        $validated['group_name'] = $this->normalizeGiftGroupName($validated['group_name'] ?? null);

        if ($gift === null) {
            if (!isset($validated['group_sort_order']) || (int) $validated['group_sort_order'] <= 0) {
                $validated['group_sort_order'] = $this->resolveGiftGroupSortOrder($validated['group_name']);
            }
            if (!isset($validated['sort_order']) || (int) $validated['sort_order'] <= 0) {
                $validated['sort_order'] = $this->nextGiftSortOrder();
            }

            return $validated;
        }

        $groupChanged = $validated['group_name'] !== $this->normalizeGiftGroupName($gift->group_name);
        if ($groupChanged) {
            $validated['group_sort_order'] = $this->resolveGiftGroupSortOrder($validated['group_name'], (int) $gift->id);
        } elseif (!isset($validated['group_sort_order']) || (int) $validated['group_sort_order'] <= 0) {
            $validated['group_sort_order'] = (int) $gift->group_sort_order;
        }

        return $validated;
    }

    private function normalizeNonNegativeNumber(array $payload, string $key): float
    {
        if (!array_key_exists($key, $payload) || $payload[$key] === null || $payload[$key] === '') {
            return 0.0;
        }

        return max((float) $payload[$key], 0.0);
    }

    private function validateBulkRows(Request $request, array $rules): array
    {
        $validated = $request->validate([
            'rows' => ['required', 'array', 'min:1', 'max:500'],
            'rows.*' => ['array'],
        ]);

        $rows = [];
        foreach ($validated['rows'] as $row) {
            $rows[] = Validator::make($row, $rules)->validate();
        }

        return $rows;
    }

    private function validateBulkIds(Request $request): array
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:1000'],
            'ids.*' => ['integer'],
        ]);

        return array_values(array_unique(array_map('intval', $validated['ids'])));
    }

    private function mergeManualExpenseBreakdown(array $validated): array
    {
        $amount = isset($validated['amount']) ? (float) $validated['amount'] : 0.0;
        $type = $validated['type'] ?? 'expense';
        $base = $type === 'budget' ? $amount : 0.0;
        $paid = $type === 'expense' ? $amount : 0.0;
        $downPayment = $paid;
        $remaining = max($paid - $downPayment, 0.0);

        return array_merge($validated, [
            'entry_mode' => 'manual',
            'source_type' => null,
            'source_id' => null,
            'base_price' => $base,
            'paid_amount' => $paid,
            'down_payment' => $downPayment,
            'remaining_amount' => $remaining,
        ]);
    }

    private function syncAutoExpenseFromTask(EngagementTask $task): void
    {
        $base = max((float) ($task->price ?? 0), 0);
        $paid = max((float) ($task->paid_amount ?? 0), 0);
        $downPayment = max((float) ($task->down_payment ?? 0), 0);
        $remaining = max($paid - $downPayment, 0);

        if ($base <= 0 && $paid <= 0 && $downPayment <= 0) {
            $this->deleteAutoExpense('task', (int) $task->id);

            return;
        }

        Expense::updateOrCreate(
            [
                'source_type' => 'task',
                'source_id' => $task->id,
            ],
            [
                'entry_mode' => 'auto',
                'name' => $task->title,
                'category' => 'To-Do',
                'type' => 'expense',
                'amount' => $downPayment,
                'base_price' => $base,
                'paid_amount' => $paid,
                'down_payment' => $downPayment,
                'remaining_amount' => $remaining,
                'notes' => $task->vendor ? 'Vendor: ' . $task->vendor : null,
            ]
        );
    }

    private function syncAutoExpenseFromGift(Gift $gift): void
    {
        $base = max((float) ($gift->price ?? 0), 0);
        $paid = max((float) ($gift->paid_amount ?? 0), 0);
        $downPayment = $paid;
        $remaining = max($paid - $downPayment, 0);

        if ($base <= 0 && $paid <= 0 && $downPayment <= 0) {
            $this->deleteAutoExpense('gift', (int) $gift->id);

            return;
        }

        Expense::updateOrCreate(
            [
                'source_type' => 'gift',
                'source_id' => $gift->id,
            ],
            [
                'entry_mode' => 'auto',
                'name' => $gift->name,
                'category' => $this->normalizeGiftGroupName($gift->group_name) ?? 'Seserahan',
                'type' => 'expense',
                'amount' => $downPayment,
                'base_price' => $base,
                'paid_amount' => $paid,
                'down_payment' => $downPayment,
                'remaining_amount' => $remaining,
                'notes' => $gift->brand ? 'Brand: ' . $gift->brand : null,
            ]
        );
    }

    private function deleteAutoExpense(string $sourceType, int $sourceId): void
    {
        Expense::query()
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->delete();
    }

    private function setClientId(Request $request): void
    {
        $clientId = $request->header('X-Client-ID', '');
        if ($clientId !== '') {
            DB::select("SELECT set_config('app.client_id', ?, false)", [$clientId]);
        }
    }

    private function nextGuestSortOrder(string $eventType, string $side): int
    {
        $max = (int) Guest::where('event_type', $eventType)
            ->where('side', $side)
            ->max('sort_order');

        return $max + 1;
    }

    private function nextGiftSortOrder(): int
    {
        $max = (int) Gift::max('sort_order');

        return $max + 1;
    }

    private function nextVendorGroupSortOrder(): int
    {
        $max = (int) Vendor::max('group_sort_order');

        return $max + 1;
    }

    private function nextGiftGroupSortOrder(): int
    {
        $max = (int) Gift::max('group_sort_order');

        return $max + 1;
    }

    private function resolveGiftGroupSortOrder(?string $groupName, ?int $ignoreGiftId = null): int
    {
        $query = Gift::query();
        if ($ignoreGiftId !== null) {
            $query->where('id', '<>', $ignoreGiftId);
        }

        if ($groupName === null) {
            $query->where(function ($q) {
                $q->whereNull('group_name')->orWhere('group_name', '');
            });
        } else {
            $query->where('group_name', $groupName);
        }

        $existing = $query->min('group_sort_order');
        if ($existing !== null && (int) $existing > 0) {
            return (int) $existing;
        }

        return $this->nextGiftGroupSortOrder();
    }

    private function resolveVendorGroupSortOrder(?string $groupName, ?int $ignoreVendorId = null): int
    {
        $query = Vendor::query();
        if ($ignoreVendorId !== null) {
            $query->where('id', '<>', $ignoreVendorId);
        }

        if ($groupName === null) {
            $query->where(function ($q) {
                $q->whereNull('group_name')->orWhere('group_name', '');
            });
        } else {
            $query->where('group_name', $groupName);
        }

        $existing = $query->min('group_sort_order');
        if ($existing !== null && (int) $existing > 0) {
            return (int) $existing;
        }

        return $this->nextVendorGroupSortOrder();
    }

    private function normalizeGiftGroupName($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function respondSuccess(Request $request, string $message, array $payload = [])
    {
        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json(array_merge(['message' => $message], $payload));
        }

        return back()->with('success', $message);
    }
}
