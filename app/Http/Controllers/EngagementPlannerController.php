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
            ->orderByRaw("status = 'complete'")
            ->orderByDesc('created_at')
            ->get();

        return view('planner.gifts', [
            'gifts' => $gifts,
            'totalGiftBudget' => (float) Gift::sum('price'),
        ]);
    }

    public function expensesPage(): View
    {
        $expenses = Expense::orderByDesc('created_at')->get();
        $totalBudget = (float) Expense::where('type', 'budget')->sum('amount');
        $totalExpense = (float) Expense::where('type', 'expense')->sum('amount');

        return view('planner.expenses', [
            'expenses' => $expenses,
            'stats' => [
                'totalBudget' => $totalBudget,
                'totalExpense' => $totalExpense,
                'remainingBudget' => $totalBudget - $totalExpense,
            ],
        ]);
    }

    public function storeGuest(Request $request)
    {
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

        DB::transaction(function () use ($validated) {
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
        $guest->delete();

        return $this->respondSuccess($request, 'Undangan dihapus.');
    }

    public function storeTask(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'vendor' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'task_status' => ['required', 'in:not_started,in_progress,done'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'finish_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $price = isset($validated['price']) ? (float) $validated['price'] : 0.0;
        $paid = isset($validated['paid_amount']) ? (float) $validated['paid_amount'] : 0.0;
        $validated['remaining_amount'] = max($price - $paid, 0);
        $validated['status'] = $validated['task_status'] === 'done' ? 'done' : 'pending';

        $task = EngagementTask::create($validated);

        return $this->respondSuccess($request, 'Task berhasil ditambahkan.', ['record' => $task]);
    }

    public function updateTask(Request $request, EngagementTask $task)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'vendor' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'task_status' => ['required', 'in:not_started,in_progress,done'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'finish_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $price = isset($validated['price']) ? (float) $validated['price'] : 0.0;
        $paid = isset($validated['paid_amount']) ? (float) $validated['paid_amount'] : 0.0;
        $validated['remaining_amount'] = max($price - $paid, 0);
        $validated['status'] = $validated['task_status'] === 'done' ? 'done' : 'pending';

        $task->update($validated);

        return $this->respondSuccess($request, 'Task diperbarui.', ['record' => $task->fresh()]);
    }

    public function destroyTask(Request $request, EngagementTask $task)
    {
        $task->delete();

        return $this->respondSuccess($request, 'Task dihapus.');
    }

    public function storeGift(Request $request)
    {
        $gift = Gift::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'link' => ['nullable', 'string', 'max:2048'],
            'status' => ['required', 'in:not_started,on_delivery,complete'],
            'notes' => ['nullable', 'string'],
        ]));

        return $this->respondSuccess($request, 'Item seserahan berhasil ditambahkan.', ['record' => $gift]);
    }

    public function updateGift(Request $request, Gift $gift)
    {
        $gift->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'link' => ['nullable', 'string', 'max:2048'],
            'status' => ['required', 'in:not_started,on_delivery,complete'],
            'notes' => ['nullable', 'string'],
        ]));

        return $this->respondSuccess($request, 'Status seserahan diperbarui.', ['record' => $gift->fresh()]);
    }

    public function destroyGift(Request $request, Gift $gift)
    {
        $gift->delete();

        return $this->respondSuccess($request, 'Item seserahan dihapus.');
    }

    public function storeExpense(Request $request)
    {
        $expense = Expense::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'type' => ['required', 'in:budget,expense'],
            'amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]));

        return $this->respondSuccess($request, 'Catatan budget/expense ditambahkan.', ['record' => $expense]);
    }

    public function updateExpense(Request $request, Expense $expense)
    {
        $expense->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'type' => ['required', 'in:budget,expense'],
            'amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]));

        return $this->respondSuccess($request, 'Catatan budget/expense diperbarui.', ['record' => $expense->fresh()]);
    }

    public function destroyExpense(Request $request, Expense $expense)
    {
        $expense->delete();

        return $this->respondSuccess($request, 'Catatan budget/expense dihapus.');
    }

    private function nextGuestSortOrder(string $eventType, string $side): int
    {
        $max = (int) Guest::where('event_type', $eventType)
            ->where('side', $side)
            ->max('sort_order');

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
