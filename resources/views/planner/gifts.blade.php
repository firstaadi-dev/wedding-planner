@extends('layouts.planner')

@section('title', 'List Seserahan')
@section('subtitle', 'Pantau item seserahan, harga, pembayaran, link, dan progres')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-12"><div class="metric-card"><div class="metric-label">Total Harga Seserahan</div><div class="metric-value" id="gift-total-price">Rp {{ number_format($totalGiftBudget, 0, ',', '.') }}</div></div></div>
</div>

<div class="planner-card">
    <div class="card-header pt-3 px-3 fw-semibold">Daftar Seserahan</div>
    <div class="card-body pt-2">
        <div class="table-responsive">
            <table class="table table-clean table-sm align-middle mb-0" data-sheet-table data-sheet-name="gifts" data-reorder-url="{{ route('gifts.reorder') }}" data-enter-next-field="name" data-create-url="{{ route('gifts.store') }}" data-update-url="/gifts/__ID__" data-delete-url="/gifts/__ID__" data-required="name,status">
                <thead>
                <tr>
                    <th>Nama</th>
                    <th>Merk</th>
                    <th>Price (Rp)</th>
                    <th>Paid Amount (Rp)</th>
                    <th>DP (Rp)</th>
                    <th>Remaining Amount (Rp)</th>
                    <th>Link</th>
                    <th>Keterangan</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                @foreach($gifts as $gift)
                    <tr data-row data-id="{{ $gift->id }}">
                        <td>
                            <input type="hidden" class="sheet-cell" data-field="sort_order" value="{{ $gift->sort_order }}">
                            <div class="name-cell">
                                <span class="drag-handle">::</span>
                                <input class="form-control form-control-sm sheet-cell" data-field="name" value="{{ $gift->name }}">
                            </div>
                        </td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="brand" value="{{ $gift->brand }}"></td>
                        <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell currency-idr" data-field="price" data-currency-idr="1" value="{{ $gift->price ?? 0 }}"></td>
                        <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell gift-paid currency-idr" data-field="paid_amount" data-currency-idr="1" value="{{ $gift->paid_amount ?? 0 }}"></td>
                        <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell gift-dp currency-idr" data-field="down_payment" data-currency-idr="1" value="{{ $gift->down_payment ?? 0 }}"></td>
                        <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell gift-remaining currency-idr" data-field="remaining_amount" data-currency-idr="1" value="{{ max(($gift->paid_amount ?? 0) - ($gift->down_payment ?? 0), 0) }}" readonly></td>
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
                    <td>
                        <input type="hidden" class="sheet-cell" data-field="sort_order" value="0">
                        <input class="form-control form-control-sm sheet-cell" data-field="name" placeholder="Nama item">
                    </td>
                    <td><input class="form-control form-control-sm sheet-cell" data-field="brand" placeholder="Merk"></td>
                    <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell currency-idr" data-field="price" data-currency-idr="1" value="Rp0"></td>
                    <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell gift-paid currency-idr" data-field="paid_amount" data-currency-idr="1" value="Rp0"></td>
                    <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell gift-dp currency-idr" data-field="down_payment" data-currency-idr="1" value="Rp0"></td>
                    <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell gift-remaining currency-idr" data-field="remaining_amount" data-currency-idr="1" value="Rp0" readonly></td>
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
                        <td>
                            <input type="hidden" class="sheet-cell" data-field="sort_order" value="0">
                            <input class="form-control form-control-sm sheet-cell" data-field="name" placeholder="Nama item">
                        </td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="brand" placeholder="Merk"></td>
                        <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell currency-idr" data-field="price" data-currency-idr="1" value="Rp0"></td>
                        <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell gift-paid currency-idr" data-field="paid_amount" data-currency-idr="1" value="Rp0"></td>
                        <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell gift-dp currency-idr" data-field="down_payment" data-currency-idr="1" value="Rp0"></td>
                        <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell gift-remaining currency-idr" data-field="remaining_amount" data-currency-idr="1" value="Rp0" readonly></td>
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
        var draggingRow = null;
        var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var clientIdValue = (window.__clientId || '');

        async function requestJson(url, method, payload) {
            var response = await fetch(url, {
                method: method,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Client-ID': clientIdValue
                },
                body: payload ? JSON.stringify(payload) : null
            });

            if (!response.ok) {
                throw new Error('Request gagal');
            }

            return response.json();
        }

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

                if (row.dataset.remainingBound !== '1') {
                    row.dataset.remainingBound = '1';
                    row.querySelectorAll('.gift-paid, .gift-dp').forEach(function (input) {
                        input.addEventListener('input', function () {
                            recalcGiftRemaining(row);
                        });
                        input.addEventListener('change', function () {
                            recalcGiftRemaining(row);
                        });
                    });
                }

                recalcGiftRemaining(row);
                bindLinkControl(row);
            });

            document.querySelectorAll('table[data-sheet-name="gifts"] tbody').forEach(function (tbody) {
                syncGiftSortOrderInputs(tbody);
            });
        }

        function refreshGiftDraggableRows() {
            document.querySelectorAll('table[data-sheet-name="gifts"] tr[data-row][data-id]').forEach(function (row) {
                row.setAttribute('draggable', 'true');
            });
        }

        function ensureGiftRowDecorations() {
            document.querySelectorAll('table[data-sheet-name="gifts"] tr[data-row][data-id]').forEach(function (row) {
                var nameInput = row.querySelector('input[data-field="name"]:not([type="hidden"])');
                if (!nameInput) return;
                if (nameInput.closest('.name-cell')) return;

                var wrapper = document.createElement('div');
                wrapper.className = 'name-cell';

                var handle = document.createElement('span');
                handle.className = 'drag-handle';
                handle.textContent = '::';

                nameInput.parentNode.insertBefore(wrapper, nameInput);
                wrapper.appendChild(handle);
                wrapper.appendChild(nameInput);
            });
        }

        function getDropBeforeRow(tbody, pointerY) {
            var rows = Array.from(tbody.querySelectorAll('tr[data-row][data-id]')).filter(function (row) {
                return row !== draggingRow;
            });

            for (var i = 0; i < rows.length; i++) {
                var box = rows[i].getBoundingClientRect();
                if (pointerY < box.top + (box.height / 2)) {
                    return rows[i];
                }
            }

            return null;
        }

        function syncGiftSortOrderInputs(tbody) {
            var order = 1;
            tbody.querySelectorAll('tr[data-row][data-id]').forEach(function (row) {
                var sortInput = row.querySelector('[data-field="sort_order"]');
                if (sortInput) sortInput.value = String(order);
                order++;
            });
        }

        async function persistGiftOrder(table) {
            if (!table || !table.dataset.reorderUrl) return;
            var ids = Array.from(table.querySelectorAll('tbody tr[data-row][data-id]')).map(function (row) {
                return Number(row.dataset.id);
            });
            if (!ids.length) return;

            await requestJson(table.dataset.reorderUrl, 'POST', {
                ordered_ids: ids
            });
        }

        function initGiftDragDrop() {
            document.querySelectorAll('table[data-sheet-name="gifts"]').forEach(function (table) {
                var tbody = table.querySelector('tbody');
                if (!tbody || tbody.dataset.dragBound === '1') return;
                tbody.dataset.dragBound = '1';

                tbody.addEventListener('dragstart', function (event) {
                    var row = event.target.closest('tr[data-row][data-id]');
                    if (!row || !event.target.closest('.drag-handle')) {
                        event.preventDefault();
                        return;
                    }
                    draggingRow = row;
                    row.classList.add('row-dragging');
                    if (event.dataTransfer) event.dataTransfer.effectAllowed = 'move';
                });

                tbody.addEventListener('dragend', function () {
                    tbody.querySelectorAll('.sheet-drop-target').forEach(function (el) {
                        el.classList.remove('sheet-drop-target');
                    });
                    if (draggingRow) draggingRow.classList.remove('row-dragging');
                    draggingRow = null;
                });

                tbody.addEventListener('dragover', function (event) {
                    if (!draggingRow) return;
                    event.preventDefault();
                    if (event.dataTransfer) event.dataTransfer.dropEffect = 'move';
                });

                tbody.addEventListener('dragleave', function (event) {
                    if (event.target === tbody) {
                        tbody.querySelectorAll('.sheet-drop-target').forEach(function (el) {
                            el.classList.remove('sheet-drop-target');
                        });
                    }
                });

                tbody.addEventListener('drop', function (event) {
                    if (!draggingRow) return;
                    event.preventDefault();

                    var dropBeforeRow = getDropBeforeRow(tbody, event.clientY);
                    var addRow = tbody.querySelector('tr[data-new-row="1"]');

                    if (dropBeforeRow) {
                        tbody.insertBefore(draggingRow, dropBeforeRow);
                    } else {
                        tbody.insertBefore(draggingRow, addRow || null);
                    }

                    syncGiftSortOrderInputs(tbody);
                    persistGiftOrder(table).catch(console.error);
                });
            });
        }

        function recalcGiftRemaining(row) {
            var paidInput = row.querySelector('.gift-paid');
            var dpInput = row.querySelector('.gift-dp');
            var remInput = row.querySelector('.gift-remaining');
            if (!paidInput || !dpInput || !remInput) return;

            var paid = parseCurrencyIdr(paidInput.value);
            var dp = parseCurrencyIdr(dpInput.value);
            var remaining = Math.max(paid - dp, 0);
            remInput.value = formatCurrencyIdr(remaining);
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
                ensureGiftRowDecorations();
                refreshGiftDraggableRows();
                recalcGiftStats();
            }
        });

        bindGiftRows();
        ensureGiftRowDecorations();
        refreshGiftDraggableRows();
        initGiftDragDrop();
    })();
</script>
@endpush
