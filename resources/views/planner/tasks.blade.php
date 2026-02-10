@extends('layouts.planner')

@section('title', 'List Persiapan')
@section('subtitle', 'Task engagement dengan tracking vendor, biaya, timeline, dan progres')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-6"><div class="metric-card"><div class="metric-label">Open (Not Started/In Progress)</div><div class="metric-value" id="task-open-count">{{ $stats['openTasks'] }}</div></div></div>
    <div class="col-md-6"><div class="metric-card"><div class="metric-label">Done</div><div class="metric-value" id="task-done-count">{{ $stats['doneTasks'] }}</div></div></div>
</div>

<div class="planner-card">
    <div class="card-header pt-3 px-3 fw-semibold">To-do Engagement</div>
    <div class="card-body pt-2">
        <div class="table-responsive">
            <table class="table table-clean table-sm align-middle mb-0" data-sheet-table data-enter-next-field="title" data-create-url="{{ route('tasks.store') }}" data-bulk-create-url="{{ route('tasks.bulk-store') }}" data-bulk-delete-url="{{ route('tasks.bulk-destroy') }}" data-update-url="/tasks/__ID__" data-delete-url="/tasks/__ID__" data-required="title,task_status">
                <thead>
                <tr>
                    <th>Task</th>
                    <th>Vendor</th>
                    <th>Harga Awal (Rp)</th>
                    <th>Harga Final (Rp)</th>
                    <th>Sudah Dibayar (Rp)</th>
                    <th>Sisa Pelunasan (Rp)</th>
                    <th>Status</th>
                    <th>Start Date</th>
                    <th>Due Date</th>
                    <th>Finish Date</th>
                    <th>Notes</th>
                </tr>
                </thead>
                <tbody>
                @foreach($tasks as $task)
                    <tr data-row data-id="{{ $task->id }}">
                        <td><input class="form-control form-control-sm sheet-cell" data-field="title" value="{{ $task->title }}"></td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="vendor" value="{{ $task->vendor }}"></td>
                        <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell task-price currency-idr" data-field="price" data-currency-idr="1" value="{{ $task->price ?? 0 }}"></td>
                        <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell task-paid currency-idr" data-field="paid_amount" data-currency-idr="1" value="{{ $task->paid_amount ?? 0 }}"></td>
                        <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell task-dp currency-idr" data-field="down_payment" data-currency-idr="1" value="{{ $task->down_payment ?? 0 }}"></td>
                        <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell task-remaining currency-idr" data-field="remaining_amount" data-currency-idr="1" value="{{ $task->remaining_amount ?? 0 }}" readonly></td>
                        <td>
                            <select class="form-select form-select-sm sheet-cell" data-field="task_status">
                                <option value="not_started" {{ $task->task_status === 'not_started' ? 'selected' : '' }}>Not Started</option>
                                <option value="in_progress" {{ $task->task_status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="done" {{ $task->task_status === 'done' ? 'selected' : '' }}>Done :)</option>
                            </select>
                        </td>
                        <td><input type="date" class="form-control form-control-sm sheet-cell" data-field="start_date" value="{{ optional($task->start_date)->format('Y-m-d') }}"></td>
                        <td><input type="date" class="form-control form-control-sm sheet-cell" data-field="due_date" value="{{ optional($task->due_date)->format('Y-m-d') }}"></td>
                        <td><input type="date" class="form-control form-control-sm sheet-cell" data-field="finish_date" value="{{ optional($task->finish_date)->format('Y-m-d') }}"></td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="notes" value="{{ $task->notes }}"></td>
                    </tr>
                @endforeach

                <tr data-row data-new-row="1" class="inline-add-row">
                    <td><input class="form-control form-control-sm sheet-cell" data-field="title" placeholder="Task"></td>
                    <td><input class="form-control form-control-sm sheet-cell" data-field="vendor" placeholder="Vendor"></td>
                    <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell task-price currency-idr" data-field="price" data-currency-idr="1" value="Rp0"></td>
                    <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell task-paid currency-idr" data-field="paid_amount" data-currency-idr="1" value="Rp0"></td>
                    <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell task-dp currency-idr" data-field="down_payment" data-currency-idr="1" value="Rp0"></td>
                    <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell task-remaining currency-idr" data-field="remaining_amount" data-currency-idr="1" value="Rp0" readonly></td>
                    <td>
                        <select class="form-select form-select-sm sheet-cell" data-field="task_status">
                            <option value="not_started" selected>Not Started</option>
                            <option value="in_progress">In Progress</option>
                            <option value="done">Done :)</option>
                        </select>
                    </td>
                    <td><input type="date" class="form-control form-control-sm sheet-cell" data-field="start_date"></td>
                    <td><input type="date" class="form-control form-control-sm sheet-cell" data-field="due_date"></td>
                    <td><input type="date" class="form-control form-control-sm sheet-cell" data-field="finish_date"></td>
                    <td><input class="form-control form-control-sm sheet-cell" data-field="notes" placeholder="Notes"></td>
                </tr>
                </tbody>

                <template data-new-row-template>
                    <tr data-row data-new-row="1" class="inline-add-row">
                        <td><input class="form-control form-control-sm sheet-cell" data-field="title" placeholder="Task"></td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="vendor" placeholder="Vendor"></td>
                        <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell task-price currency-idr" data-field="price" data-currency-idr="1" value="Rp0"></td>
                        <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell task-paid currency-idr" data-field="paid_amount" data-currency-idr="1" value="Rp0"></td>
                        <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell task-dp currency-idr" data-field="down_payment" data-currency-idr="1" value="Rp0"></td>
                        <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell task-remaining currency-idr" data-field="remaining_amount" data-currency-idr="1" value="Rp0" readonly></td>
                        <td>
                            <select class="form-select form-select-sm sheet-cell" data-field="task_status">
                                <option value="not_started" selected>Not Started</option>
                                <option value="in_progress">In Progress</option>
                                <option value="done">Done :)</option>
                            </select>
                        </td>
                        <td><input type="date" class="form-control form-control-sm sheet-cell" data-field="start_date"></td>
                        <td><input type="date" class="form-control form-control-sm sheet-cell" data-field="due_date"></td>
                        <td><input type="date" class="form-control form-control-sm sheet-cell" data-field="finish_date"></td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="notes" placeholder="Notes"></td>
                    </tr>
                </template>
            </table>
        </div>
    </div>
</div>
@endsection

@push('page-scripts')
<script>
    (function () {
        function parseCurrencyIdr(value) {
            if (value === null || value === undefined) return 0;
            const raw = String(value).trim();
            if (!raw) return 0;
            let normalized = raw.replace(/[^\d,.\-]/g, '');
            if (normalized.includes(',') && normalized.includes('.')) {
                if (normalized.lastIndexOf(',') > normalized.lastIndexOf('.')) {
                    normalized = normalized.replace(/\./g, '').replace(',', '.');
                } else {
                    normalized = normalized.replace(/,/g, '');
                }
            } else if (normalized.includes(',') && !normalized.includes('.')) {
                const parts = normalized.split(',');
                if (parts.length === 2 && parts[1].length <= 2) {
                    normalized = parts[0].replace(/\./g, '') + '.' + parts[1];
                } else {
                    normalized = normalized.replace(/,/g, '');
                }
            } else if (normalized.includes('.')) {
                const parts = normalized.split('.');
                if (!(parts.length === 2 && parts[1].length <= 2)) {
                    normalized = normalized.replace(/\./g, '');
                }
            }
            const parsed = Number(normalized);
            return Number.isFinite(parsed) ? parsed : 0;
        }

        function formatCurrencyIdr(value) {
            const amount = Number(value);
            if (!Number.isFinite(amount) || amount <= 0) return 'Rp0';
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0,
            }).format(amount);
        }

        function bindCurrencyInput(input) {
            if (!input || input.dataset.currencyBound === '1' || input.readOnly) return;
            input.dataset.currencyBound = '1';

            input.value = formatCurrencyIdr(parseCurrencyIdr(input.value));

            input.addEventListener('focus', function () {
                const numeric = parseCurrencyIdr(input.value);
                input.value = numeric > 0 ? String(Math.round(numeric)) : '';
            });

            input.addEventListener('blur', function () {
                input.value = formatCurrencyIdr(parseCurrencyIdr(input.value));
            });
        }

        function recalcTaskRemaining(row) {
            const paidInput = row.querySelector('.task-paid');
            const dpInput = row.querySelector('.task-dp');
            const remInput = row.querySelector('.task-remaining');
            if (!paidInput || !dpInput || !remInput) return;

            const paid = parseCurrencyIdr(paidInput.value);
            const dp = parseCurrencyIdr(dpInput.value);
            const remaining = Math.max(paid - dp, 0);
            remInput.value = formatCurrencyIdr(remaining);
        }

        function bindTaskRows() {
            document.querySelectorAll('table[data-create-url="{{ route('tasks.store') }}"] tbody tr[data-row]').forEach(function (row) {
                if (row.dataset.remainingBound === '1') return;
                row.dataset.remainingBound = '1';

                row.querySelectorAll('.currency-idr').forEach(function (input) {
                    bindCurrencyInput(input);
                });

                row.querySelectorAll('.task-paid, .task-dp').forEach(function (input) {
                    input.addEventListener('input', function () {
                        recalcTaskRemaining(row);
                    });
                    input.addEventListener('change', function () {
                        recalcTaskRemaining(row);
                    });
                });

                recalcTaskRemaining(row);
            });
        }

        function recalcTaskStats() {
            var rows = document.querySelectorAll('table[data-create-url="{{ route('tasks.store') }}"] tbody tr[data-row][data-id]');
            var openCount = 0;
            var doneCount = 0;

            rows.forEach(function (row) {
                var statusInput = row.querySelector('[data-field="task_status"]');
                if (!statusInput) return;

                if (statusInput.value === 'done') {
                    doneCount += 1;
                } else if (statusInput.value === 'not_started' || statusInput.value === 'in_progress') {
                    openCount += 1;
                }
            });

            var openEl = document.getElementById('task-open-count');
            var doneEl = document.getElementById('task-done-count');
            if (openEl) openEl.textContent = String(openCount);
            if (doneEl) doneEl.textContent = String(doneCount);
        }

        document.addEventListener('sheet:changed', function (event) {
            const table = event.detail && event.detail.table;
            if (table && table.dataset.createUrl === '{{ route('tasks.store') }}') {
                bindTaskRows();
                recalcTaskStats();
            }
        });

        document.addEventListener('change', function (event) {
            var target = event.target;
            if (!target) return;
            if (target.dataset.field === 'task_status' && target.closest('table[data-create-url="{{ route('tasks.store') }}"]')) {
                recalcTaskStats();
            }
        });

        bindTaskRows();
        recalcTaskStats();
    })();
</script>
@endpush
