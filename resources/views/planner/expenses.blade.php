@extends('layouts.planner')

@section('title', 'Budget & Expense')
@section('subtitle', 'Ringkas pemasukan budget, pengeluaran, dan sisa dana')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Total Budget (Tipe Budget)</div><div class="metric-value" id="expense-total-budget">Rp {{ number_format($stats['totalBudget'], 0, ',', '.') }}</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Total Expense (Manual + Paid)</div><div class="metric-value" id="expense-total-expense">Rp {{ number_format($stats['totalExpense'], 0, ',', '.') }}</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Sisa Budget</div><div class="metric-value" id="expense-remaining-budget">Rp {{ number_format($stats['remainingBudget'], 0, ',', '.') }}</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Total Hutang / Remaining</div><div class="metric-value" id="expense-total-debt">Rp {{ number_format($stats['totalDebt'], 0, ',', '.') }}</div></div></div>
</div>

<div class="planner-card mb-4">
    <div class="card-header pt-3 px-3 fw-semibold">One-Glance Budget Story</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="metric-card h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="metric-label mb-0">Budget Health</div>
                        <span id="budget-health-chip" class="badge text-bg-success">Aman</span>
                    </div>
                    <div class="metric-value" id="budget-health-value">0%</div>
                    <div class="small text-muted mt-1" id="budget-health-note">Expense masih jauh dari batas budget.</div>
                    <div class="progress mt-3" role="progressbar" aria-label="Budget health">
                        <div id="budget-health-bar" class="progress-bar bg-success" style="width: 0%"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="metric-card h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="metric-label mb-0">Sisa Bayar Coverage</div>
                        <span id="coverage-chip" class="badge text-bg-success">Cukup</span>
                    </div>
                    <div class="metric-value" id="coverage-gap-value">Rp 0</div>
                    <div class="small text-muted mt-1" id="coverage-note">Budget tersisa cukup untuk bayar semua sisa.</div>
                    <div class="progress mt-3" role="progressbar" aria-label="Coverage">
                        <div id="coverage-bar" class="progress-bar bg-success" style="width: 0%"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="metric-card h-100">
                    <div class="metric-label mb-2">Penghematan</div>
                    <div class="metric-value" id="savings-percentage-value">{{ number_format($stats['savingsPercentage'], 1) }}%</div>
                    <div class="small text-muted mt-1" id="savings-nominal-note">Nominal hemat: Rp {{ number_format($stats['totalSavings'], 0, ',', '.') }}</div>
                    <div class="progress mt-3" role="progressbar" aria-label="Savings percentage">
                        <div id="savings-percentage-bar" class="progress-bar bg-info" style="width: {{ min(max($stats['savingsPercentage'], 0), 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="planner-card mb-4">
    <div class="card-header pt-3 px-3 fw-semibold">Budget & Expense Manual</div>
    <div class="card-body pt-2">
        <div class="table-responsive">
            <table class="table table-clean table-sm align-middle mb-0" data-sheet-table data-create-url="{{ route('expenses.store') }}" data-update-url="/expenses/__ID__" data-delete-url="/expenses/__ID__" data-required="name,type,amount">
                <thead>
                <tr><th>Nama</th><th>Tipe</th><th>Jumlah</th><th>Notes</th><th class="row-actions">Aksi</th></tr>
                </thead>
                <tbody>
                @foreach($manualExpenses as $expense)
                    <tr data-row data-id="{{ $expense->id }}">
                        <td><input class="form-control form-control-sm sheet-cell" data-field="name" value="{{ $expense->name }}"></td>
                        <td>
                            <select class="form-select form-select-sm sheet-cell" data-field="type">
                                <option value="budget" {{ $expense->type === 'budget' ? 'selected' : '' }}>Budget</option>
                                <option value="expense" {{ $expense->type === 'expense' ? 'selected' : '' }}>Expense</option>
                            </select>
                        </td>
                        <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell currency-idr" data-field="amount" data-currency-idr="1" value="{{ $expense->amount ?? 0 }}"></td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="notes" value="{{ $expense->notes }}"></td>
                        <td class="row-actions">
                            <details class="row-menu">
                                <summary>...</summary>
                                <div class="row-menu-panel">
                                    <button type="button" data-delete-row>Hapus</button>
                                </div>
                            </details>
                        </td>
                    </tr>
                @endforeach

                <tr data-row data-new-row="1" class="inline-add-row">
                    <td><input class="form-control form-control-sm sheet-cell" data-field="name" placeholder="Nama transaksi"></td>
                    <td>
                        <select class="form-select form-select-sm sheet-cell" data-field="type">
                            <option value="budget" selected>Budget</option>
                            <option value="expense">Expense</option>
                        </select>
                    </td>
                    <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell currency-idr" data-field="amount" data-currency-idr="1" value="Rp0"></td>
                    <td><input class="form-control form-control-sm sheet-cell" data-field="notes" placeholder="Catatan"></td>
                    <td class="row-actions"></td>
                </tr>
                </tbody>

                <template data-new-row-template>
                    <tr data-row data-new-row="1" class="inline-add-row">
                        <td><input class="form-control form-control-sm sheet-cell" data-field="name" placeholder="Nama transaksi"></td>
                        <td>
                            <select class="form-select form-select-sm sheet-cell" data-field="type">
                                <option value="budget" selected>Budget</option>
                                <option value="expense">Expense</option>
                            </select>
                        </td>
                        <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell currency-idr" data-field="amount" data-currency-idr="1" value="Rp0"></td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="notes" placeholder="Catatan"></td>
                        <td class="row-actions"></td>
                    </tr>
                </template>
            </table>
        </div>
    </div>
</div>

<div class="planner-card">
    <div class="card-header pt-3 px-3 fw-semibold">Auto Tracking dari To-do & Seserahan</div>
    <div class="card-body pt-2">
        <div class="table-responsive">
            <table class="table table-clean table-sm align-middle mb-0">
                <thead>
                <tr>
                    <th>Sumber</th>
                    <th>Nama</th>
                    <th>Base Price</th>
                    <th>Paid Amount</th>
                    <th>DP</th>
                    <th>Remaining Amount</th>
                    <th>Catatan</th>
                </tr>
                </thead>
                <tbody>
                @forelse($autoExpenses as $expense)
                    <tr>
                        <td>{{ $expense->source_type === 'task' ? 'To-do' : 'Seserahan' }}</td>
                        <td>{{ $expense->name }}</td>
                        <td>Rp {{ number_format($expense->base_price, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($expense->paid_amount, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($expense->down_payment, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($expense->remaining_amount, 0, ',', '.') }}</td>
                        <td>{{ $expense->notes }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada data auto tracking. Isi harga/paid di To-do atau Seserahan dulu.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('page-scripts')
<script>
    (function () {
        var state = {
            manualBudget: Number(@json($stats['manualBudget'])) || 0,
            manualExpense: Number(@json($stats['manualExpense'])) || 0,
            autoBase: Number(@json($stats['autoBase'])) || 0,
            autoPaid: Number(@json($stats['autoPaid'])) || 0,
            autoRemaining: Number(@json($stats['autoRemaining'])) || 0,
            totalSavings: Number(@json($stats['totalSavings'])) || 0,
            totalDebt: Number(@json($stats['totalDebt'])) || 0,
            savingsPercentage: Number(@json($stats['savingsPercentage'])) || 0,
        };

        function formatRupiah(amount) {
            if (!Number.isFinite(amount) || amount === 0) return 'Rp 0';
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(amount));
        }

        function parseCurrencyIdr(value) {
            if (value === null || value === undefined) return 0;
            var raw = String(value).trim();
            if (!raw) return 0;
            var normalized = raw.replace(/[^\d,.\-]/g, '');
            if (normalized.indexOf(',') !== -1 && normalized.indexOf('.') !== -1) {
                if (normalized.lastIndexOf(',') > normalized.lastIndexOf('.')) {
                    normalized = normalized.replace(/\./g, '').replace(',', '.');
                } else {
                    normalized = normalized.replace(/,/g, '');
                }
            } else if (normalized.indexOf(',') !== -1 && normalized.indexOf('.') === -1) {
                var partsComma = normalized.split(',');
                if (partsComma.length === 2 && partsComma[1].length <= 2) {
                    normalized = partsComma[0].replace(/\./g, '') + '.' + partsComma[1];
                } else {
                    normalized = normalized.replace(/,/g, '');
                }
            } else if (normalized.indexOf('.') !== -1) {
                var partsDot = normalized.split('.');
                if (!(partsDot.length === 2 && partsDot[1].length <= 2)) {
                    normalized = normalized.replace(/\./g, '');
                }
            }
            var parsed = Number(normalized);
            return Number.isFinite(parsed) ? parsed : 0;
        }

        function formatCurrencyIdr(value) {
            var amount = Number(value);
            if (!Number.isFinite(amount) || amount <= 0) return 'Rp0';
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0
            }).format(amount);
        }

        function bindCurrencyInput(input) {
            if (!input || input.dataset.currencyBound === '1') return;
            input.dataset.currencyBound = '1';

            input.value = formatCurrencyIdr(parseCurrencyIdr(input.value));

            input.addEventListener('focus', function () {
                var numeric = parseCurrencyIdr(input.value);
                input.value = numeric > 0 ? String(Math.round(numeric)) : '';
            });

            input.addEventListener('blur', function () {
                input.value = formatCurrencyIdr(parseCurrencyIdr(input.value));
            });
        }

        function bindAmountCurrencyInputs() {
            document.querySelectorAll('table[data-create-url="{{ route('expenses.store') }}"] .currency-idr').forEach(function (input) {
                bindCurrencyInput(input);
            });
        }

        function getTotals() {
            var totalBudget = state.manualBudget;
            var totalExpense = state.manualExpense + state.autoPaid;
            return {
                totalBudget: totalBudget,
                totalExpense: totalExpense,
                remainingBudget: totalBudget - totalExpense,
            };
        }

        function getStoryData() {
            var totals = getTotals();
            var budget = Math.max(totals.totalBudget, 0);
            var expense = Math.max(totals.totalExpense, 0);
            var remainingBudget = totals.remainingBudget;
            var payable = Math.max(state.autoRemaining, 0);
            var usagePct = budget > 0 ? (expense / budget) * 100 : (expense > 0 ? 999 : 0);
            var coverageGap = remainingBudget - payable;
            var coveragePct = payable > 0 ? (Math.max(remainingBudget, 0) / payable) * 100 : 100;

            return {
                totals: totals,
                budget: budget,
                expense: expense,
                remainingBudget: remainingBudget,
                payable: payable,
                usagePct: usagePct,
                coverageGap: coverageGap,
                coveragePct: coveragePct,
            };
        }

        function getBudgetHealthMeta(usagePct) {
            if (usagePct > 100) {
                return { label: 'Over Budget', chipClass: 'text-bg-danger', barClass: 'bg-danger', note: 'Expense melewati budget. Perlu penyesuaian segera.' };
            }
            if (usagePct > 90) {
                return { label: 'Mepet', chipClass: 'text-bg-warning', barClass: 'bg-warning', note: 'Expense hampir menyentuh batas budget.' };
            }
            if (usagePct > 70) {
                return { label: 'Waspada', chipClass: 'text-bg-info', barClass: 'bg-info', note: 'Budget masih aman, tapi ruang sisa mulai terbatas.' };
            }

            return { label: 'Aman', chipClass: 'text-bg-success', barClass: 'bg-success', note: 'Expense masih jauh dari batas budget.' };
        }

        function setBadgeClass(el, className) {
            if (!el) return;
            el.classList.remove('text-bg-success', 'text-bg-info', 'text-bg-warning', 'text-bg-danger');
            el.classList.add(className);
        }

        function setProgressClass(el, className) {
            if (!el) return;
            el.classList.remove('bg-success', 'bg-info', 'bg-warning', 'bg-danger');
            el.classList.add(className);
        }

        function repaintStoryPanel() {
            var story = getStoryData();
            var usageMeta = getBudgetHealthMeta(story.usagePct);
            var usagePercentDisplay = Math.max(0, story.usagePct);
            var usagePercentBar = Math.min(Math.max(usagePercentDisplay, 0), 100);
            var coveragePercentDisplay = Math.max(0, story.coveragePct);
            var coveragePercentBar = Math.min(coveragePercentDisplay, 100);
            var coverageEnough = story.coverageGap >= 0;

            var healthValue = document.getElementById('budget-health-value');
            var healthChip = document.getElementById('budget-health-chip');
            var healthNote = document.getElementById('budget-health-note');
            var healthBar = document.getElementById('budget-health-bar');
            var coverageChip = document.getElementById('coverage-chip');
            var coverageGap = document.getElementById('coverage-gap-value');
            var coverageNote = document.getElementById('coverage-note');
            var coverageBar = document.getElementById('coverage-bar');
            var savingsValue = document.getElementById('savings-percentage-value');
            var savingsNote = document.getElementById('savings-nominal-note');
            var savingsBar = document.getElementById('savings-percentage-bar');

            if (healthValue) healthValue.textContent = usagePercentDisplay.toFixed(1) + '%';
            if (healthChip) healthChip.textContent = usageMeta.label;
            if (healthNote) healthNote.textContent = usageMeta.note;
            if (healthBar) healthBar.style.width = usagePercentBar + '%';
            setBadgeClass(healthChip, usageMeta.chipClass);
            setProgressClass(healthBar, usageMeta.barClass);

            if (coverageChip) coverageChip.textContent = coverageEnough ? 'Cukup' : 'Kurang';
            if (coverageGap) coverageGap.textContent = (coverageEnough ? '+' : '-') + ' ' + formatRupiah(Math.abs(story.coverageGap));
            if (coverageNote) coverageNote.textContent = coverageEnough
                ? 'Budget tersisa cukup untuk bayar semua sisa.'
                : 'Budget tersisa belum cukup untuk menutup sisa bayar.';
            if (coverageBar) coverageBar.style.width = coveragePercentBar + '%';
            setBadgeClass(coverageChip, coverageEnough ? 'text-bg-success' : 'text-bg-danger');
            setProgressClass(coverageBar, coverageEnough ? 'bg-success' : 'bg-danger');

            if (savingsValue) savingsValue.textContent = Math.max(0, state.savingsPercentage).toFixed(1) + '%';
            if (savingsNote) savingsNote.textContent = 'Nominal hemat: ' + formatRupiah(state.totalSavings);
            if (savingsBar) savingsBar.style.width = Math.min(Math.max(state.savingsPercentage, 0), 100) + '%';
        }

        function repaintMetrics() {
            var totals = getTotals();
            var el1 = document.getElementById('expense-total-budget');
            var el2 = document.getElementById('expense-total-expense');
            var el3 = document.getElementById('expense-remaining-budget');
            var el4 = document.getElementById('expense-total-debt');
            if (el1) el1.textContent = formatRupiah(totals.totalBudget);
            if (el2) el2.textContent = formatRupiah(totals.totalExpense);
            if (el3) el3.textContent = formatRupiah(totals.remainingBudget);
            if (el4) el4.textContent = formatRupiah(state.totalDebt);
        }

        function recalcManualStats() {
            var rows = document.querySelectorAll('table[data-sheet-table] tbody tr[data-row][data-id]');
            var totalBudget = 0;
            var totalExpense = 0;

            rows.forEach(function (row) {
                var typeInput = row.querySelector('[data-field="type"]');
                var amountInput = row.querySelector('[data-field="amount"]');
                if (!typeInput || !amountInput) return;

                var amount = parseCurrencyIdr(amountInput.value);
                if (typeInput.value === 'budget') totalBudget += amount;
                if (typeInput.value === 'expense') totalExpense += amount;
            });

            state.manualBudget = totalBudget;
            state.manualExpense = totalExpense;

            repaintMetrics();
            repaintStoryPanel();
        }

        document.addEventListener('sheet:changed', function (event) {
            var table = event.detail && event.detail.table;
            if (table && table.dataset.createUrl && table.dataset.createUrl.indexOf('/expenses') !== -1) {
                bindAmountCurrencyInputs();
                recalcManualStats();
            }
        });

        document.addEventListener('change', function (event) {
            if (event.target.closest('table[data-sheet-table]') && (event.target.dataset.field === 'type' || event.target.dataset.field === 'amount')) {
                recalcManualStats();
            }
        });

        bindAmountCurrencyInputs();
        repaintMetrics();
        repaintStoryPanel();
    })();
</script>
@endpush
