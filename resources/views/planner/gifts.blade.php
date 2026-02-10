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
            <table class="table table-clean table-sm align-middle mb-0" data-sheet-table data-sheet-name="gifts" data-reorder-url="{{ route('gifts.reorder') }}" data-reorder-groups-url="{{ route('gifts.reorder-groups') }}" data-enter-next-field="name" data-create-url="{{ route('gifts.store') }}" data-update-url="/gifts/__ID__" data-delete-url="/gifts/__ID__" data-required="name,status">
                <thead>
                <tr>
                    <th>Nama</th>
                    <th>Merk</th>
                    <th>Group</th>
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
                @php
                    $currentGroup = null;
                    $currentGroupKey = null;
                    $groupIndex = 0;
                    $groupTotals = $gifts->groupBy(function ($item) {
                        $label = trim((string) $item->group_name);
                        return $label !== '' ? $label : 'Tanpa Group';
                    })->map(function ($items) {
                        return (float) $items->sum('price');
                    });
                @endphp
                @foreach($gifts as $gift)
                    @php
                        $groupKey = trim((string) $gift->group_name);
                        $groupLabel = $groupKey !== '' ? $groupKey : 'Tanpa Group';
                    @endphp
                    @if($groupLabel !== $currentGroup || $groupKey !== $currentGroupKey)
                        @if($groupIndex > 0)
                            <tr class="gift-group-gap"><td colspan="10"></td></tr>
                        @endif
                        <tr class="gift-group-separator" data-group-key="{{ $groupKey }}">
                            <td colspan="10">
                                <div class="gift-group-header">
                                    <div class="gift-group-title-wrap">
                                        <span class="gift-group-title">{{ $groupLabel }}</span>
                                        <span class="gift-group-total">Total Price: Rp {{ number_format($groupTotals[$groupLabel] ?? 0, 0, ',', '.') }}</span>
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
                                <input class="form-control form-control-sm sheet-cell" data-field="name" value="{{ $gift->name }}">
                            </div>
                        </td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="brand" value="{{ $gift->brand }}"></td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="group_name" value="{{ $gift->group_name }}" placeholder="Nama group"></td>
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
                        <input type="hidden" class="sheet-cell" data-field="group_sort_order" value="0">
                        <input class="form-control form-control-sm sheet-cell" data-field="name" placeholder="Nama item">
                    </td>
                    <td><input class="form-control form-control-sm sheet-cell" data-field="brand" placeholder="Merk"></td>
                    <td><input class="form-control form-control-sm sheet-cell" data-field="group_name" placeholder="Nama group"></td>
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
                            <input type="hidden" class="sheet-cell" data-field="group_sort_order" value="0">
                            <input class="form-control form-control-sm sheet-cell" data-field="name" placeholder="Nama item">
                        </td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="brand" placeholder="Merk"></td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="group_name" placeholder="Nama group"></td>
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

                recalcGiftRemaining(row);
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
            td.colSpan = 10;
            tr.appendChild(td);
            return tr;
        }

        function buildGroupSeparatorRow(groupKey, label, totalPrice) {
            var tr = document.createElement('tr');
            tr.className = 'gift-group-separator';
            tr.dataset.groupKey = groupKey;
            var td = document.createElement('td');
            td.colSpan = 10;
            td.innerHTML = '' +
                '<div class="gift-group-header">' +
                    '<div class="gift-group-title-wrap">' +
                        '<span class="gift-group-title">' + label + '</span>' +
                        '<span class="gift-group-total">Total Price: ' + formatCurrencyIdr(totalPrice) + '</span>' +
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

        function getGroupTotalsMap(rows) {
            var totals = new Map();
            rows.forEach(function (row) {
                var groupInput = row.querySelector('[data-field="group_name"]');
                var groupKey = normalizeGroupValue(groupInput ? groupInput.value : '');
                var priceInput = row.querySelector('[data-field="price"]');
                var price = priceInput ? parseCurrencyIdr(priceInput.value) : 0;
                totals.set(groupKey, (totals.get(groupKey) || 0) + price);
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
            var groupTotals = getGroupTotalsMap(rows);

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
                var totalPrice = groupTotals.get(groupKey) || 0;
                tbody.insertBefore(buildGroupSeparatorRow(groupKey, groupLabel, totalPrice), groupRows[0]);
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

                    regroupGiftRows(table);
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
                regroupGiftRows(table);
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
