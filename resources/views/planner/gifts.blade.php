@extends('layouts.planner')

@section('title', 'List Seserahan')
@section('subtitle', 'Pantau item seserahan, harga, pembayaran, link, dan progres')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-6"><div class="metric-card"><div class="metric-label">Total Harga Awal Seserahan</div><div class="metric-value" id="gift-total-price">Rp {{ number_format($totalGiftBudget, 0, ',', '.') }}</div></div></div>
    <div class="col-md-6"><div class="metric-card"><div class="metric-label">Total Harga Final Semua Kategori</div><div class="metric-value" id="gift-total-final-all">Rp {{ number_format($totalGiftFinal, 0, ',', '.') }}</div></div></div>
</div>

<div class="planner-card">
    <div class="card-header pt-3 px-3 fw-semibold">Daftar Seserahan</div>
    <div class="card-body pt-2">
        <div class="table-responsive">
            <table class="table table-clean table-sm align-middle mb-0" data-sheet-table data-sheet-name="gifts" data-reorder-url="{{ route('gifts.reorder') }}" data-reorder-groups-url="{{ route('gifts.reorder-groups') }}" data-enter-next-field="name" data-create-url="{{ route('gifts.store') }}" data-bulk-create-url="{{ route('gifts.bulk-store') }}" data-bulk-delete-url="{{ route('gifts.bulk-destroy') }}" data-update-url="/gifts/__ID__" data-delete-url="/gifts/__ID__" data-required="name,status">
                <thead>
                <tr>
                    <th>Nama</th>
                    <th>Merk</th>
                    <th>Group</th>
                    <th>Harga Awal (Rp)</th>
                    <th>Harga Final (Rp)</th>
                    <th>Link</th>
                    <th>Keterangan</th>
                    <th>Status</th>
                    <th class="row-actions">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @php
                    $currentGroup = null;
                    $currentGroupKey = null;
                    $groupIndex = 0;
                    $groupInitialTotals = $gifts->groupBy(function ($item) {
                        $label = trim((string) $item->group_name);
                        return $label !== '' ? $label : 'Tanpa Group';
                    })->map(function ($items) {
                        return (float) $items->sum('price');
                    });
                    $groupFinalTotals = $gifts->groupBy(function ($item) {
                        $label = trim((string) $item->group_name);
                        return $label !== '' ? $label : 'Tanpa Group';
                    })->map(function ($items) {
                        return (float) $items->sum('paid_amount');
                    });
                @endphp
                @foreach($gifts as $gift)
                    @php
                        $groupKey = trim((string) $gift->group_name);
                        $groupLabel = $groupKey !== '' ? $groupKey : 'Tanpa Group';
                    @endphp
                    @if($groupLabel !== $currentGroup || $groupKey !== $currentGroupKey)
                        @if($groupIndex > 0)
                            <tr class="gift-group-gap"><td colspan="9"></td></tr>
                        @endif
                        <tr class="gift-group-separator" data-group-key="{{ $groupKey }}">
                            <td colspan="9">
                                <div class="gift-group-header">
                                    <div class="gift-group-title-wrap">
                                        <span class="gift-group-title">{{ $groupLabel }}</span>
                                        <span class="gift-group-total gift-group-total-initial">Total Harga Awal: Rp {{ number_format($groupInitialTotals[$groupLabel] ?? 0, 0, ',', '.') }}</span>
                                        <span class="gift-group-total gift-group-total-final">Total Harga Final: Rp {{ number_format($groupFinalTotals[$groupLabel] ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="gift-group-actions">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-add-to-group="1" title="Tambah item ke group ini">+ Item</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-move-group="up" title="Naikkan group">↑</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-move-group="down" title="Turunkan group">↓</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @php
                            $currentGroup = $groupLabel;
                            $currentGroupKey = $groupKey;
                            $groupIndex++;
                        @endphp
                    @endif
                    <tr data-row data-id="{{ $gift->id }}">
                        <td>
                            <input type="hidden" class="sheet-cell" data-field="sort_order" value="{{ $gift->sort_order }}">
                            <input type="hidden" class="sheet-cell" data-field="group_sort_order" value="{{ $gift->group_sort_order }}">
                            <div class="name-cell">
                                <span class="drag-handle">::</span>
                                <textarea rows="1" class="form-control form-control-sm sheet-cell" data-field="name">{{ $gift->name }}</textarea>
                            </div>
                        </td>
                        <td><textarea rows="1" class="form-control form-control-sm sheet-cell" data-field="brand">{{ $gift->brand }}</textarea></td>
                        <td><textarea rows="1" class="form-control form-control-sm sheet-cell" data-field="group_name" placeholder="Nama group">{{ $gift->group_name }}</textarea></td>
                        <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell currency-idr" data-field="price" data-currency-idr="1" value="{{ $gift->price ?? 0 }}"></td>
                        <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell gift-paid currency-idr" data-field="paid_amount" data-currency-idr="1" value="{{ $gift->paid_amount ?? 0 }}"></td>
                        <td>
                            <div class="input-group input-group-sm">
                                <input class="form-control form-control-sm sheet-cell" data-field="link" value="{{ $gift->link }}" placeholder="https://...">
                                <a class="btn btn-outline-secondary" data-open-link target="_blank" rel="noopener noreferrer" href="#" title="Buka link di tab baru" aria-label="Buka link di tab baru">↗</a>
                            </div>
                        </td>
                        <td><textarea rows="1" class="form-control form-control-sm sheet-cell" data-field="notes">{{ $gift->notes }}</textarea></td>
                        <td>
                            <select class="form-select form-select-sm sheet-cell" data-field="status">
                                <option value="not_started" {{ $gift->status === 'not_started' ? 'selected' : '' }}>Not Started</option>
                                <option value="on_delivery" {{ $gift->status === 'on_delivery' ? 'selected' : '' }}>On Delivery</option>
                                <option value="complete" {{ $gift->status === 'complete' ? 'selected' : '' }}>Complete</option>
                            </select>
                        </td>
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
                    <td>
                        <input type="hidden" class="sheet-cell" data-field="sort_order" value="0">
                        <input type="hidden" class="sheet-cell" data-field="group_sort_order" value="0">
                        <textarea rows="1" class="form-control form-control-sm sheet-cell" data-field="name" placeholder="Nama item"></textarea>
                    </td>
                    <td><textarea rows="1" class="form-control form-control-sm sheet-cell" data-field="brand" placeholder="Merk"></textarea></td>
                    <td><textarea rows="1" class="form-control form-control-sm sheet-cell" data-field="group_name" placeholder="Nama group"></textarea></td>
                    <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell currency-idr" data-field="price" data-currency-idr="1" value="Rp0"></td>
                    <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell gift-paid currency-idr" data-field="paid_amount" data-currency-idr="1" value="Rp0"></td>
                    <td>
                        <div class="input-group input-group-sm">
                            <input class="form-control form-control-sm sheet-cell" data-field="link" placeholder="https://...">
                            <a class="btn btn-outline-secondary disabled" data-open-link target="_blank" rel="noopener noreferrer" href="#" title="Buka link di tab baru" aria-label="Buka link di tab baru" aria-disabled="true" tabindex="-1">↗</a>
                        </div>
                    </td>
                    <td><textarea rows="1" class="form-control form-control-sm sheet-cell" data-field="notes" placeholder="Keterangan"></textarea></td>
                    <td>
                        <select class="form-select form-select-sm sheet-cell" data-field="status">
                            <option value="not_started" selected>Not Started</option>
                            <option value="on_delivery">On Delivery</option>
                            <option value="complete">Complete</option>
                        </select>
                    </td>
                    <td class="row-actions"></td>
                </tr>
                </tbody>

                <template data-new-row-template>
                    <tr data-row data-new-row="1" class="inline-add-row">
                        <td>
                            <input type="hidden" class="sheet-cell" data-field="sort_order" value="0">
                            <input type="hidden" class="sheet-cell" data-field="group_sort_order" value="0">
                            <textarea rows="1" class="form-control form-control-sm sheet-cell" data-field="name" placeholder="Nama item"></textarea>
                        </td>
                        <td><textarea rows="1" class="form-control form-control-sm sheet-cell" data-field="brand" placeholder="Merk"></textarea></td>
                        <td><textarea rows="1" class="form-control form-control-sm sheet-cell" data-field="group_name" placeholder="Nama group"></textarea></td>
                        <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell currency-idr" data-field="price" data-currency-idr="1" value="Rp0"></td>
                        <td><input type="text" inputmode="numeric" class="form-control form-control-sm sheet-cell gift-paid currency-idr" data-field="paid_amount" data-currency-idr="1" value="Rp0"></td>
                        <td>
                            <div class="input-group input-group-sm">
                                <input class="form-control form-control-sm sheet-cell" data-field="link" placeholder="https://...">
                                <a class="btn btn-outline-secondary disabled" data-open-link target="_blank" rel="noopener noreferrer" href="#" title="Buka link di tab baru" aria-label="Buka link di tab baru" aria-disabled="true" tabindex="-1">↗</a>
                            </div>
                        </td>
                        <td><textarea rows="1" class="form-control form-control-sm sheet-cell" data-field="notes" placeholder="Keterangan"></textarea></td>
                        <td>
                            <select class="form-select form-select-sm sheet-cell" data-field="status">
                                <option value="not_started" selected>Not Started</option>
                                <option value="on_delivery">On Delivery</option>
                                <option value="complete">Complete</option>
                            </select>
                        </td>
                        <td class="row-actions"></td>
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
        var giftReorderController = null;

        async function requestJson(url, method, payload, signal) {
            var response = await fetch(url, {
                method: method,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Client-ID': clientIdValue
                },
                body: payload ? JSON.stringify(payload) : null,
                signal: signal || undefined
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

                if (row.dataset.groupBound !== '1') {
                    row.dataset.groupBound = '1';
                    var groupInput = row.querySelector('[data-field="group_name"]');
                    if (groupInput) {
                        groupInput.addEventListener('change', function () {
                            var table = row.closest('table[data-sheet-name="gifts"]');
                            if (table) {
                                regroupGiftRows(table);
                            }
                        });
                    }
                }

                bindLinkControl(row);
            });

            document.querySelectorAll('table[data-sheet-name="gifts"] tbody').forEach(function (tbody) {
                syncGiftSortOrderInputs(tbody);
            });
        }

        function normalizeGroupValue(value) {
            return String(value || '').trim();
        }

        function clearGiftGroupVisualRows(tbody) {
            tbody.querySelectorAll('tr.gift-group-separator, tr.gift-group-gap').forEach(function (row) {
                row.remove();
            });
        }

        function buildGroupGapRow() {
            var tr = document.createElement('tr');
            tr.className = 'gift-group-gap';
            var td = document.createElement('td');
            td.colSpan = 9;
            tr.appendChild(td);
            return tr;
        }

        function buildGroupSeparatorRow(groupKey, label, totalInitial, totalFinal) {
            var tr = document.createElement('tr');
            tr.className = 'gift-group-separator';
            tr.dataset.groupKey = groupKey;
            var td = document.createElement('td');
            td.colSpan = 9;
            td.innerHTML = '' +
                '<div class="gift-group-header">' +
                    '<div class="gift-group-title-wrap">' +
                        '<span class="gift-group-title">' + label + '</span>' +
                        '<span class="gift-group-total gift-group-total-initial">Total Harga Awal: ' + formatCurrencyIdr(totalInitial) + '</span>' +
                        '<span class="gift-group-total gift-group-total-final">Total Harga Final: ' + formatCurrencyIdr(totalFinal) + '</span>' +
                    '</div>' +
                    '<div class="gift-group-actions">' +
                        '<button type="button" class="btn btn-sm btn-outline-secondary" data-add-to-group="1" title="Tambah item ke group ini">+ Item</button>' +
                        '<button type="button" class="btn btn-sm btn-outline-secondary" data-move-group="up" title="Naikkan group">↑</button>' +
                        '<button type="button" class="btn btn-sm btn-outline-secondary" data-move-group="down" title="Turunkan group">↓</button>' +
                    '</div>' +
                '</div>';
            tr.appendChild(td);
            return tr;
        }

        function getGroupSortFromRow(row) {
            var input = row.querySelector('[data-field="group_sort_order"]');
            var value = input ? parseInt(input.value || '0', 10) : 0;
            return Number.isFinite(value) && value > 0 ? value : 999999;
        }

        function setGroupSortForRow(row, sortOrder) {
            var input = row.querySelector('[data-field="group_sort_order"]');
            if (input) input.value = String(sortOrder);
        }

        function resolveGroupSortOrderForValue(table, groupValue) {
            var normalized = normalizeGroupValue(groupValue);
            var groupSort = 0;

            table.querySelectorAll('tr[data-row][data-id]').forEach(function (row) {
                var groupInput = row.querySelector('[data-field="group_name"]');
                if (!groupInput) return;
                if (normalizeGroupValue(groupInput.value) !== normalized) return;
                var rowSort = getGroupSortFromRow(row);
                if (!groupSort || rowSort < groupSort) {
                    groupSort = rowSort;
                }
            });

            if (groupSort > 0) {
                return groupSort;
            }

            var maxSort = 0;
            table.querySelectorAll('tr[data-row][data-id]').forEach(function (row) {
                var rowSort = getGroupSortFromRow(row);
                if (rowSort > maxSort && rowSort < 999999) {
                    maxSort = rowSort;
                }
            });

            return maxSort + 1;
        }

        function syncGroupSortFromGroupNames(table) {
            if (!table) return;
            table.querySelectorAll('tr[data-row][data-id]').forEach(function (row) {
                var groupInput = row.querySelector('[data-field="group_name"]');
                if (!groupInput) return;
                var groupSort = resolveGroupSortOrderForValue(table, groupInput.value);
                setGroupSortForRow(row, groupSort);
            });
        }

        function getGroupRowsMap(rows) {
            var groupMap = new Map();
            rows.forEach(function (row) {
                var groupInput = row.querySelector('[data-field="group_name"]');
                var groupKey = normalizeGroupValue(groupInput ? groupInput.value : '');
                if (!groupMap.has(groupKey)) {
                    groupMap.set(groupKey, []);
                }
                groupMap.get(groupKey).push(row);
            });
            return groupMap;
        }

        function getGroupInitialTotalsMap(rows) {
            var totals = new Map();
            rows.forEach(function (row) {
                var groupInput = row.querySelector('[data-field="group_name"]');
                var groupKey = normalizeGroupValue(groupInput ? groupInput.value : '');
                var priceInput = row.querySelector('[data-field="price"]');
                var initialPrice = priceInput ? parseCurrencyIdr(priceInput.value) : 0;
                totals.set(groupKey, (totals.get(groupKey) || 0) + initialPrice);
            });
            return totals;
        }

        function getGroupFinalTotalsMap(rows) {
            var totals = new Map();
            rows.forEach(function (row) {
                var groupInput = row.querySelector('[data-field="group_name"]');
                var groupKey = normalizeGroupValue(groupInput ? groupInput.value : '');
                var paidInput = row.querySelector('[data-field="paid_amount"]');
                var finalPrice = paidInput ? parseCurrencyIdr(paidInput.value) : 0;
                totals.set(groupKey, (totals.get(groupKey) || 0) + finalPrice);
            });
            return totals;
        }

        function persistGiftGroupOrder(table) {
            if (!table) return Promise.resolve();
            var url = table.dataset.reorderGroupsUrl;
            if (!url) return Promise.resolve();

            var orderedGroups = [];
            table.querySelectorAll('tr.gift-group-separator').forEach(function (row) {
                orderedGroups.push(row.dataset.groupKey || '');
            });

            return requestJson(url, 'POST', {
                ordered_groups: orderedGroups
            });
        }

        function regroupGiftRows(table) {
            if (!table) return;
            var tbody = table.querySelector('tbody');
            if (!tbody) return;

            clearGiftGroupVisualRows(tbody);

            var addRow = tbody.querySelector('tr[data-new-row="1"]');
            var rows = Array.from(tbody.querySelectorAll('tr[data-row][data-id]'));
            if (!rows.length) return;

            syncGroupSortFromGroupNames(table);

            rows.sort(function (a, b) {
                var aGroupSort = getGroupSortFromRow(a);
                var bGroupSort = getGroupSortFromRow(b);
                if (aGroupSort !== bGroupSort) return aGroupSort - bGroupSort;

                var aGroupInput = a.querySelector('[data-field="group_name"]');
                var bGroupInput = b.querySelector('[data-field="group_name"]');
                var aGroup = normalizeGroupValue(aGroupInput ? aGroupInput.value : '').toLowerCase();
                var bGroup = normalizeGroupValue(bGroupInput ? bGroupInput.value : '').toLowerCase();
                if (aGroup < bGroup) return -1;
                if (aGroup > bGroup) return 1;

                var aSortInput = a.querySelector('[data-field="sort_order"]');
                var bSortInput = b.querySelector('[data-field="sort_order"]');
                var aSort = aSortInput ? parseInt(aSortInput.value || '0', 10) : 0;
                var bSort = bSortInput ? parseInt(bSortInput.value || '0', 10) : 0;
                if (aSort !== bSort) return aSort - bSort;

                var aId = parseInt(a.dataset.id || '0', 10);
                var bId = parseInt(b.dataset.id || '0', 10);
                return aId - bId;
            });

            rows.forEach(function (row) {
                tbody.insertBefore(row, addRow || null);
            });

            syncGiftSortOrderInputs(tbody);
            var groupRowsMap = getGroupRowsMap(rows);
            var groupInitialTotals = getGroupInitialTotalsMap(rows);
            var groupFinalTotals = getGroupFinalTotalsMap(rows);

            var orderedGroupKeys = [];
            rows.forEach(function (row) {
                var groupInput = row.querySelector('[data-field="group_name"]');
                var key = normalizeGroupValue(groupInput ? groupInput.value : '');
                if (orderedGroupKeys.indexOf(key) === -1) {
                    orderedGroupKeys.push(key);
                }
            });

            orderedGroupKeys.forEach(function (groupKey, index) {
                var groupRows = groupRowsMap.get(groupKey) || [];
                if (!groupRows.length) return;
                if (index > 0) {
                    tbody.insertBefore(buildGroupGapRow(), groupRows[0]);
                }
                var groupLabel = groupKey || 'Tanpa Group';
                var totalInitial = groupInitialTotals.get(groupKey) || 0;
                var totalFinal = groupFinalTotals.get(groupKey) || 0;
                tbody.insertBefore(buildGroupSeparatorRow(groupKey, groupLabel, totalInitial, totalFinal), groupRows[0]);
            });
        }

        function moveGiftGroup(table, groupKey, direction) {
            if (!table) return;
            var tbody = table.querySelector('tbody');
            if (!tbody) return;

            var separators = Array.from(tbody.querySelectorAll('tr.gift-group-separator'));
            var keys = separators.map(function (row) { return row.dataset.groupKey || ''; });
            var currentIndex = keys.indexOf(groupKey);
            if (currentIndex === -1) return;

            var targetIndex = direction === 'up' ? currentIndex - 1 : currentIndex + 1;
            if (targetIndex < 0 || targetIndex >= keys.length) return;

            var temp = keys[currentIndex];
            keys[currentIndex] = keys[targetIndex];
            keys[targetIndex] = temp;

            var order = 1;
            keys.forEach(function (key) {
                table.querySelectorAll('tr[data-row][data-id]').forEach(function (row) {
                    var groupInput = row.querySelector('[data-field="group_name"]');
                    if (!groupInput) return;
                    if (normalizeGroupValue(groupInput.value) !== key) return;
                    setGroupSortForRow(row, order);
                });
                order++;
            });

            regroupGiftRows(table);
            persistGiftGroupOrder(table).catch(console.error);
        }

        function refreshGiftDraggableRows() {
            document.querySelectorAll('table[data-sheet-name="gifts"] tr[data-row][data-id]').forEach(function (row) {
                row.setAttribute('draggable', 'true');
            });
        }

        function ensureGiftRowDecorations() {
            document.querySelectorAll('table[data-sheet-name="gifts"] tr[data-row][data-id]').forEach(function (row) {
                var nameInput = row.querySelector('[data-field="name"]:not([type="hidden"])');
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

            var signal = null;
            if (typeof AbortController !== 'undefined') {
                if (giftReorderController) {
                    giftReorderController.abort();
                }
                giftReorderController = new AbortController();
                signal = giftReorderController.signal;
            }

            try {
                await requestJson(table.dataset.reorderUrl, 'POST', {
                    ordered_ids: ids
                }, signal);
            } catch (error) {
                if (error && error.name === 'AbortError') {
                    return;
                }
                throw error;
            }
        }

        function initGiftGroupReorderControls() {
            document.querySelectorAll('table[data-sheet-name="gifts"]').forEach(function (table) {
                var tbody = table.querySelector('tbody');
                if (!tbody || tbody.dataset.groupMoveBound === '1') return;
                tbody.dataset.groupMoveBound = '1';

                tbody.addEventListener('click', function (event) {
                    var addButton = event.target.closest('[data-add-to-group]');
                    if (addButton) {
                        var addSeparatorRow = addButton.closest('tr.gift-group-separator');
                        if (!addSeparatorRow) return;
                        event.preventDefault();
                        fillNewGiftRowForGroup(table, addSeparatorRow.dataset.groupKey || '');
                        return;
                    }

                    var button = event.target.closest('[data-move-group]');
                    if (!button) return;

                    var separatorRow = button.closest('tr.gift-group-separator');
                    if (!separatorRow) return;

                    event.preventDefault();
                    moveGiftGroup(table, separatorRow.dataset.groupKey || '', button.dataset.moveGroup);
                });
            });
        }

        function fillNewGiftRowForGroup(table, groupKey) {
            if (!table) return;
            var tbody = table.querySelector('tbody');
            if (!tbody) return;

            var addRow = tbody.querySelector('tr[data-new-row="1"]');
            if (!addRow) return;

            var nameInput = addRow.querySelector('[data-field="name"]');
            var groupInput = addRow.querySelector('[data-field="group_name"]');
            var groupSortInput = addRow.querySelector('[data-field="group_sort_order"]');
            if (!nameInput || !groupInput || !groupSortInput) return;

            groupInput.value = groupKey || '';
            groupSortInput.value = String(resolveGroupSortOrderForValue(table, groupKey || ''));

            var separatorRows = Array.from(tbody.querySelectorAll('tr.gift-group-separator'));
            var targetSeparator = separatorRows.find(function (row) {
                return (row.dataset.groupKey || '') === (groupKey || '');
            });
            if (targetSeparator) {
                var cursor = targetSeparator.nextElementSibling;
                var lastRowInGroup = null;

                while (cursor) {
                    if (cursor === addRow) {
                        cursor = cursor.nextElementSibling;
                        continue;
                    }
                    if (cursor.matches('tr.gift-group-separator') || cursor.matches('tr.gift-group-gap')) {
                        break;
                    }
                    if (cursor.matches('tr[data-row][data-id]')) {
                        lastRowInGroup = cursor;
                    }
                    cursor = cursor.nextElementSibling;
                }

                if (lastRowInGroup) {
                    if (lastRowInGroup.nextElementSibling) {
                        tbody.insertBefore(addRow, lastRowInGroup.nextElementSibling);
                    } else {
                        tbody.appendChild(addRow);
                    }
                } else {
                    if (targetSeparator.nextElementSibling) {
                        tbody.insertBefore(addRow, targetSeparator.nextElementSibling);
                    } else {
                        tbody.appendChild(addRow);
                    }
                }
            }

            nameInput.focus();
            if (nameInput.select) nameInput.select();
        }

        function initGiftDragDrop() {
            document.querySelectorAll('table[data-sheet-name="gifts"]').forEach(function (table) {
                var tbody = table.querySelector('tbody');
                if (!tbody || tbody.dataset.dragBound === '1') return;
                tbody.dataset.dragBound = '1';

                tbody.addEventListener('dragstart', function (event) {
                    var row = event.target.closest('tr[data-row][data-id]');
                    if (!row) return;
                    draggingRow = row;
                    row.classList.add('row-dragging');
                    if (event.dataTransfer) {
                        event.dataTransfer.effectAllowed = 'move';
                        event.dataTransfer.setData('text/plain', row.dataset.id || '');
                    }
                });

                tbody.addEventListener('dragend', function () {
                    tbody.classList.remove('sheet-drop-target');
                    if (draggingRow) draggingRow.classList.remove('row-dragging');
                    draggingRow = null;
                });

                tbody.addEventListener('dragover', function (event) {
                    if (!draggingRow) return;
                    event.preventDefault();
                    tbody.classList.add('sheet-drop-target');
                });

                tbody.addEventListener('dragleave', function (event) {
                    if (!tbody.contains(event.relatedTarget)) tbody.classList.remove('sheet-drop-target');
                });

                tbody.addEventListener('drop', function (event) {
                    if (!draggingRow) return;
                    event.preventDefault();
                    tbody.classList.remove('sheet-drop-target');

                    var dropBeforeRow = getDropBeforeRow(tbody, event.clientY);
                    var addRow = tbody.querySelector('tr[data-new-row="1"]');

                    if (dropBeforeRow) {
                        tbody.insertBefore(draggingRow, dropBeforeRow);
                    } else {
                        tbody.insertBefore(draggingRow, addRow || null);
                    }

                    syncGiftSortOrderInputs(tbody);
                    regroupGiftRows(table);
                    persistGiftOrder(table).catch(console.error);
                });
            });
        }

        function recalcGiftStats() {
            var rows = document.querySelectorAll('table[data-sheet-table] tbody tr[data-row][data-id]');
            var totalPrice = 0;
            var totalFinal = 0;
            rows.forEach(function (row) {
                var priceInput = row.querySelector('[data-field="price"]');
                if (priceInput) {
                    totalPrice += parseCurrencyIdr(priceInput.value);
                }
                var paidInput = row.querySelector('[data-field="paid_amount"]');
                if (paidInput) {
                    totalFinal += parseCurrencyIdr(paidInput.value);
                }
            });
            var el = document.getElementById('gift-total-price');
            if (el) {
                el.textContent = (totalPrice > 0) ? formatCurrencyIdr(totalPrice) : 'Rp0';
            }
            var finalEl = document.getElementById('gift-total-final-all');
            if (finalEl) {
                finalEl.textContent = (totalFinal > 0) ? formatCurrencyIdr(totalFinal) : 'Rp0';
            }
        }

        function recalcGiftGroupTotals(table) {
            if (!table) return;
            var tbody = table.querySelector('tbody');
            if (!tbody) return;

            var rows = Array.from(tbody.querySelectorAll('tr[data-row][data-id]'));
            var groupInitialTotals = getGroupInitialTotalsMap(rows);
            var groupFinalTotals = getGroupFinalTotalsMap(rows);

            tbody.querySelectorAll('tr.gift-group-separator').forEach(function (separator) {
                var groupKey = separator.dataset.groupKey || '';
                var totalInitial = groupInitialTotals.get(groupKey) || 0;
                var totalFinal = groupFinalTotals.get(groupKey) || 0;

                var initialEl = separator.querySelector('.gift-group-total-initial');
                if (initialEl) {
                    initialEl.textContent = 'Total Harga Awal: ' + formatCurrencyIdr(totalInitial);
                }

                var finalEl = separator.querySelector('.gift-group-total-final');
                if (finalEl) {
                    finalEl.textContent = 'Total Harga Final: ' + formatCurrencyIdr(totalFinal);
                }
            });
        }

        document.addEventListener('sheet:changed', function (event) {
            const table = event.detail && event.detail.table;
            if (table && table.dataset.createUrl === '{{ route('gifts.store') }}') {
                var rowCount = table.querySelectorAll('tbody tr[data-row][data-id]').length;
                var previousRowCount = parseInt(table.dataset.rowCount || String(rowCount), 10);
                if (!Number.isFinite(previousRowCount)) {
                    previousRowCount = rowCount;
                }

                bindGiftRows();
                ensureGiftRowDecorations();
                refreshGiftDraggableRows();
                if (rowCount !== previousRowCount) {
                    regroupGiftRows(table);
                } else {
                    recalcGiftGroupTotals(table);
                }
                table.dataset.rowCount = String(rowCount);
                recalcGiftStats();
            }
        });

        bindGiftRows();
        ensureGiftRowDecorations();
        refreshGiftDraggableRows();
        initGiftDragDrop();
        initGiftGroupReorderControls();
        document.querySelectorAll('table[data-sheet-name="gifts"]').forEach(function (table) {
            regroupGiftRows(table);
            table.dataset.rowCount = String(table.querySelectorAll('tbody tr[data-row][data-id]').length);
        });
    })();
</script>
<style>
    table[data-sheet-name="gifts"] th {
        padding-top: 0.85rem;
        padding-bottom: 0.85rem;
    }

    table[data-sheet-name="gifts"] td {
        padding-top: 0.55rem;
        padding-bottom: 0.55rem;
    }

    table[data-sheet-name="gifts"] .form-control.form-control-sm,
    table[data-sheet-name="gifts"] .form-select.form-select-sm {
        min-height: 34px;
    }

    .gift-group-gap td {
        height: 12px;
        padding: 0 !important;
        border: 0 !important;
        background: transparent !important;
    }

    .gift-group-separator td {
        background: #f1e9dc !important;
        color: #6e5f51;
        font-weight: 700;
        letter-spacing: 0.3px;
        border-top: 2px solid #d9c9b6;
        border-bottom: 1px solid #e6dacc;
        padding-top: 0.42rem !important;
        padding-bottom: 0.42rem !important;
    }

    .gift-group-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .gift-group-title-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .gift-group-title {
        font-weight: 700;
    }

    .gift-group-total {
        font-weight: 600;
        color: #88725a;
        font-size: 0.82rem;
    }

    .gift-group-actions {
        display: inline-flex;
        gap: 6px;
        flex-shrink: 0;
    }

    .gift-group-actions .btn {
        padding: 0.1rem 0.42rem;
        line-height: 1;
        font-size: 0.78rem;
    }
</style>
@endpush
