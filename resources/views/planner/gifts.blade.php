@extends('layouts.planner')

@section('title', 'List Seserahan')
@section('subtitle', 'Pantau item seserahan, harga, pembayaran, link, dan progres')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-12"><div class="metric-card"><div class="metric-label">Total Harga Seserahan</div><div class="metric-value" id="gift-total-price">Rp {{ number_format($totalGiftBudget, 0, ',', '.') }}</div></div></div>
</div>

<div class="planner-card">
    <div class="card-header bg-white border-0 pt-3 px-3 fw-semibold">Daftar Seserahan</div>
    <div class="card-body pt-2">
        <div class="table-responsive">
            <table class="table table-clean table-sm align-middle mb-0" data-sheet-table data-enter-next-field="name" data-create-url="{{ route('gifts.store') }}" data-update-url="/gifts/__ID__" data-delete-url="/gifts/__ID__" data-required="name,status">
                <thead>
                <tr>
                    <th>Nama</th>
                    <th>Merk</th>
                    <th>Price (Rp)</th>
                    <th>Paid Amount (Rp)</th>
                    <th>Link</th>
                    <th>Keterangan</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                @foreach($gifts as $gift)
                    <tr data-row data-id="{{ $gift->id }}">
                        <td><input class="form-control form-control-sm sheet-cell" data-field="name" value="{{ $gift->name }}"></td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="brand" value="{{ $gift->brand }}"></td>
                        <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell currency-idr" data-field="price" data-currency-idr="1" value="{{ $gift->price ?? 0 }}"></td>
                        <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell currency-idr" data-field="paid_amount" data-currency-idr="1" value="{{ $gift->paid_amount ?? 0 }}"></td>
                        <td>
                            <div class="input-group input-group-sm">
                                <input class="form-control form-control-sm sheet-cell" data-field="link" value="{{ $gift->link }}" placeholder="https://...">
                                <a class="btn btn-outline-secondary" data-open-link target="_blank" rel="noopener noreferrer" href="#" title="Buka link di tab baru" aria-label="Buka link di tab baru">↗</a>
                            </div>
                        </td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="notes" value="{{ $gift->notes }}"></td>
                        <td>
                            <select class="form-select form-select-sm sheet-cell" data-field="status">
                                <option value="not_started" {{ $gift->status === 'not_started' ? 'selected' : '' }}>Not Started</option>
                                <option value="on_delivery" {{ $gift->status === 'on_delivery' ? 'selected' : '' }}>On Delivery</option>
                                <option value="complete" {{ $gift->status === 'complete' ? 'selected' : '' }}>Complete</option>
                            </select>
                        </td>
                    </tr>
                @endforeach

                <tr data-row data-new-row="1" class="inline-add-row">
                    <td><input class="form-control form-control-sm sheet-cell" data-field="name" placeholder="Nama item"></td>
                    <td><input class="form-control form-control-sm sheet-cell" data-field="brand" placeholder="Merk"></td>
                    <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell currency-idr" data-field="price" data-currency-idr="1" value="Rp0"></td>
                    <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell currency-idr" data-field="paid_amount" data-currency-idr="1" value="Rp0"></td>
                    <td>
                        <div class="input-group input-group-sm">
                            <input class="form-control form-control-sm sheet-cell" data-field="link" placeholder="https://...">
                            <a class="btn btn-outline-secondary disabled" data-open-link target="_blank" rel="noopener noreferrer" href="#" title="Buka link di tab baru" aria-label="Buka link di tab baru" aria-disabled="true" tabindex="-1">↗</a>
                        </div>
                    </td>
                    <td><input class="form-control form-control-sm sheet-cell" data-field="notes" placeholder="Keterangan"></td>
                    <td>
                        <select class="form-select form-select-sm sheet-cell" data-field="status">
                            <option value="not_started" selected>Not Started</option>
                            <option value="on_delivery">On Delivery</option>
                            <option value="complete">Complete</option>
                        </select>
                    </td>
                </tr>
                </tbody>

                <template data-new-row-template>
                    <tr data-row data-new-row="1" class="inline-add-row">
                        <td><input class="form-control form-control-sm sheet-cell" data-field="name" placeholder="Nama item"></td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="brand" placeholder="Merk"></td>
                        <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell currency-idr" data-field="price" data-currency-idr="1" value="Rp0"></td>
                        <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell currency-idr" data-field="paid_amount" data-currency-idr="1" value="Rp0"></td>
                        <td>
                            <div class="input-group input-group-sm">
                                <input class="form-control form-control-sm sheet-cell" data-field="link" placeholder="https://...">
                                <a class="btn btn-outline-secondary disabled" data-open-link target="_blank" rel="noopener noreferrer" href="#" title="Buka link di tab baru" aria-label="Buka link di tab baru" aria-disabled="true" tabindex="-1">↗</a>
                            </div>
                        </td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="notes" placeholder="Keterangan"></td>
                        <td>
                            <select class="form-select form-select-sm sheet-cell" data-field="status">
                                <option value="not_started" selected>Not Started</option>
                                <option value="on_delivery">On Delivery</option>
                                <option value="complete">Complete</option>
                            </select>
                        </td>
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
            if (!input || input.dataset.currencyBound === '1') return;
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

        function normalizeLink(raw) {
            var value = (raw || '').trim();
            if (!value) return '';
            if (!/^https?:\/\//i.test(value)) {
                value = 'https://' + value;
            }
            return value;
        }

        function updateOpenLinkButton(row) {
            var input = row.querySelector('[data-field="link"]');
            var button = row.querySelector('[data-open-link]');
            if (!input || !button) return;

            var url = normalizeLink(input.value);
            if (url) {
                button.href = url;
                button.classList.remove('disabled');
                button.removeAttribute('aria-disabled');
                button.removeAttribute('tabindex');
            } else {
                button.href = '#';
                button.classList.add('disabled');
                button.setAttribute('aria-disabled', 'true');
                button.setAttribute('tabindex', '-1');
            }
        }

        function bindLinkControl(row) {
            if (!row) return;
            if (row.dataset.linkReady === '1') {
                updateOpenLinkButton(row);
                return;
            }
            row.dataset.linkReady = '1';

            var input = row.querySelector('[data-field="link"]');
            var button = row.querySelector('[data-open-link]');
            if (!input || !button) return;

            updateOpenLinkButton(row);

            input.addEventListener('input', function () {
                updateOpenLinkButton(row);
            });

            input.addEventListener('blur', function () {
                updateOpenLinkButton(row);
            });

            button.addEventListener('click', function (event) {
                if (button.classList.contains('disabled')) {
                    event.preventDefault();
                }
            });
        }

        function bindGiftRows() {
            document.querySelectorAll('table[data-create-url="{{ route('gifts.store') }}"] tbody tr[data-row]').forEach(function (row) {
                if (row.dataset.currencyReady !== '1') {
                    row.dataset.currencyReady = '1';
                    row.querySelectorAll('.currency-idr').forEach(function (input) {
                        bindCurrencyInput(input);
                    });
                }
                bindLinkControl(row);
            });
        }

        function recalcGiftStats() {
            var rows = document.querySelectorAll('table[data-sheet-table] tbody tr[data-row][data-id]');
            var totalPrice = 0;
            rows.forEach(function (row) {
                var priceInput = row.querySelector('[data-field="price"]');
                if (!priceInput) return;
                totalPrice += parseCurrencyIdr(priceInput.value);
            });
            var el = document.getElementById('gift-total-price');
            if (el) {
                el.textContent = (totalPrice > 0) ? formatCurrencyIdr(totalPrice) : 'Rp0';
            }
        }

        document.addEventListener('sheet:changed', function (event) {
            const table = event.detail && event.detail.table;
            if (table && table.dataset.createUrl === '{{ route('gifts.store') }}') {
                bindGiftRows();
                recalcGiftStats();
            }
        });

        bindGiftRows();
    })();
</script>
@endpush
