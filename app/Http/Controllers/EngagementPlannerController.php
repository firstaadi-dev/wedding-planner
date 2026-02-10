<?php

namespace App\Http\Controllers;

use App\Models\EngagementTask;
use App\Models\Expense;
use App\Models\Gift;
use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        return view('planner.tasks', [
            'tasks' => $tasks,
            'stats' => [
                'openTasks' => $tasks->whereIn('task_status', ['not_started', 'in_progress'])->count(),
                'doneTasks' => $tasks->where('task_status', 'done')->count(),
            ],
        ]);
    }

    public function giftsPage(): View
    {
        $gifts = Gift::query()
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        return view('planner.gifts', [
            'gifts' => $gifts,
            'totalGiftBudget' => (float) Gift::sum('price'),
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

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'side' => ['required', 'in:cpp,cpw'],
            'event_type' => ['required', 'in:lamaran,resepsi'],
            'attendance_status' => ['required', 'in:invited,attending,not_attending'],
            'phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['sort_order'] = $this->nextGuestSortOrder($validated['event_type'], $validated['side']);

        $guest = Guest::create($validated);

        return $this->respondSuccess($request, 'Undangan berhasil ditambahkan.', ['record' => $guest]);
    }

    public function updateGuest(Request $request, Guest $guest)
    {
        $this->setClientId($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'side' => ['required', 'in:cpp,cpw'],
            'event_type' => ['required', 'in:lamaran,resepsi'],
            'attendance_status' => ['required', 'in:invited,attending,not_attending'],
            'phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

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
            'ordered_ids.*' => ['integer', 'exists:guests,id'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $this->setClientId($request);
            $order = 1;
            foreach ($validated['ordered_ids'] as $id) {
                Guest::where('id', $id)->update([
                    'event_type' => $validated['event_type'],
                    'side' => $validated['side'],
                    'sort_order' => $order,
                ]);
                $order++;
            }
        });

        return response()->json(['message' => 'Urutan undangan diperbarui.']);
    }

    public function destroyGuest(Request $request, Guest $guest)
    {
        $this->setClientId($request);
        $guest->delete();

        return $this->respondSuccess($request, 'Undangan dihapus.');
    }

    public function storeTask(Request $request)
    {
        $this->setClientId($request);

        $validated = $request->validate([
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
        ]);

        $paid = isset($validated['paid_amount']) ? (float) $validated['paid_amount'] : 0.0;
        $dp = isset($validated['down_payment']) ? (float) $validated['down_payment'] : 0.0;
        $validated['remaining_amount'] = max($paid - $dp, 0);
        $validated['status'] = $validated['task_status'] === 'done' ? 'done' : 'pending';

        $task = DB::transaction(function () use ($validated) {
            $task = EngagementTask::create($validated);
            $this->syncAutoExpenseFromTask($task);

            return $task;
        });

        return $this->respondSuccess($request, 'Task berhasil ditambahkan.', ['record' => $task]);
    }

    public function updateTask(Request $request, EngagementTask $task)
    {
        $this->setClientId($request);

        $validated = $request->validate([
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
        ]);

        $paid = isset($validated['paid_amount']) ? (float) $validated['paid_amount'] : 0.0;
        $dp = isset($validated['down_payment']) ? (float) $validated['down_payment'] : 0.0;
        $validated['remaining_amount'] = max($paid - $dp, 0);
        $validated['status'] = $validated['task_status'] === 'done' ? 'done' : 'pending';

        DB::transaction(function () use ($task, $validated) {
            $task->update($validated);
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

    public function storeGift(Request $request)
    {
        $this->setClientId($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'down_payment' => ['nullable', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'link' => ['nullable', 'string', 'max:2048'],
            'status' => ['required', 'in:not_started,on_delivery,complete'],
            'notes' => ['nullable', 'string'],
        ]);
        if (!isset($validated['sort_order']) || (int) $validated['sort_order'] <= 0) {
            $validated['sort_order'] = $this->nextGiftSortOrder();
        }

        $gift = DB::transaction(function () use ($validated) {
            $gift = Gift::create($validated);
            $this->syncAutoExpenseFromGift($gift);

            return $gift;
        });

        return $this->respondSuccess($request, 'Item seserahan berhasil ditambahkan.', ['record' => $gift]);
    }

    public function updateGift(Request $request, Gift $gift)
    {
        $this->setClientId($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'down_payment' => ['nullable', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'link' => ['nullable', 'string', 'max:2048'],
            'status' => ['required', 'in:not_started,on_delivery,complete'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($gift, $validated) {
            $gift->update($validated);
            $this->syncAutoExpenseFromGift($gift->fresh());
        });

        return $this->respondSuccess($request, 'Status seserahan diperbarui.', ['record' => $gift->fresh()]);
    }

    public function reorderGifts(Request $request)
    {
        $validated = $request->validate([
            'ordered_ids' => ['required', 'array'],
            'ordered_ids.*' => ['integer', 'exists:gifts,id'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $this->setClientId($request);
            $order = 1;
            foreach ($validated['ordered_ids'] as $id) {
                Gift::where('id', $id)->update(['sort_order' => $order]);
                $order++;
            }
        });

        return response()->json(['message' => 'Urutan seserahan diperbarui.']);
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

    public function storeExpense(Request $request)
    {
        $this->setClientId($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'type' => ['required', 'in:budget,expense'],
            'amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);
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

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'type' => ['required', 'in:budget,expense'],
            'amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);
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
                'category' => 'To-do',
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
        $downPayment = max((float) ($gift->down_payment ?? 0), 0);
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
                'category' => 'Seserahan',
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

    private function respondSuccess(Request $request, string $message, array $payload = [])
    {
        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json(array_merge(['message' => $message], $payload));
        }

        return back()->with('success', $message);
    }
}
