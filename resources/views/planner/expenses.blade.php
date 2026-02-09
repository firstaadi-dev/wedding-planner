@extends('layouts.planner')

@section('title', 'Budget & Expense')
@section('subtitle', 'Ringkas pemasukan budget, pengeluaran, dan sisa dana')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="metric-card"><div class="metric-label">Total Budget</div><div class="metric-value">Rp {{ number_format($stats['totalBudget'], 0, ',', '.') }}</div></div></div>
    <div class="col-md-4"><div class="metric-card"><div class="metric-label">Total Expense</div><div class="metric-value">Rp {{ number_format($stats['totalExpense'], 0, ',', '.') }}</div></div></div>
    <div class="col-md-4"><div class="metric-card"><div class="metric-label">Sisa Budget</div><div class="metric-value">Rp {{ number_format($stats['remainingBudget'], 0, ',', '.') }}</div></div></div>
</div>

<div class="planner-card">
    <div class="card-header bg-white border-0 pt-3 px-3 fw-semibold">Budget & Expense Tracker</div>
    <div class="card-body pt-2">
        <div class="table-responsive">
            <table class="table table-clean table-sm align-middle mb-0" data-sheet-table data-create-url="{{ route('expenses.store') }}" data-update-url="/expenses/__ID__" data-delete-url="/expenses/__ID__" data-required="name,type,amount">
                <thead>
                <tr><th>Nama</th><th>Kategori</th><th>Tipe</th><th>Jumlah</th><th>Notes</th><th class="row-actions">Aksi</th></tr>
                </thead>
                <tbody>
                @foreach($expenses as $expense)
                    <tr data-row data-id="{{ $expense->id }}">
                        <td><input class="form-control form-control-sm sheet-cell" data-field="name" value="{{ $expense->name }}"></td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="category" value="{{ $expense->category }}"></td>
                        <td>
                            <select class="form-select form-select-sm sheet-cell" data-field="type">
                                <option value="budget" {{ $expense->type === 'budget' ? 'selected' : '' }}>Budget</option>
                                <option value="expense" {{ $expense->type === 'expense' ? 'selected' : '' }}>Expense</option>
                            </select>
                        </td>
                        <td><input type="number" step="0.01" min="0" class="form-control form-control-sm sheet-cell" data-field="amount" value="{{ $expense->amount }}"></td>
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
                    <td><input class="form-control form-control-sm sheet-cell" data-field="category" placeholder="Kategori"></td>
                    <td>
                        <select class="form-select form-select-sm sheet-cell" data-field="type">
                            <option value="budget" selected>Budget</option>
                            <option value="expense">Expense</option>
                        </select>
                    </td>
                    <td><input type="number" step="0.01" min="0" class="form-control form-control-sm sheet-cell" data-field="amount" placeholder="Nominal"></td>
                    <td><input class="form-control form-control-sm sheet-cell" data-field="notes" placeholder="Catatan"></td>
                    <td class="row-actions"></td>
                </tr>
                </tbody>

                <template data-new-row-template>
                    <tr data-row data-new-row="1" class="inline-add-row">
                        <td><input class="form-control form-control-sm sheet-cell" data-field="name" placeholder="Nama transaksi"></td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="category" placeholder="Kategori"></td>
                        <td>
                            <select class="form-select form-select-sm sheet-cell" data-field="type">
                                <option value="budget" selected>Budget</option>
                                <option value="expense">Expense</option>
                            </select>
                        </td>
                        <td><input type="number" step="0.01" min="0" class="form-control form-control-sm sheet-cell" data-field="amount" placeholder="Nominal"></td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="notes" placeholder="Catatan"></td>
                        <td class="row-actions"></td>
                    </tr>
                </template>
            </table>
        </div>
    </div>
</div>
@endsection
