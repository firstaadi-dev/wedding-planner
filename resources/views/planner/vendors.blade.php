@extends('layouts.planner')

@section('title', 'Vendor Tracker')
@section('subtitle', 'Track kontak, reference, dan progres vendor')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="metric-card"><div class="metric-label">Total Vendor</div><div class="metric-value" id="vendor-total-count">{{ $stats['totalVendors'] }}</div></div></div>
    <div class="col-md-4"><div class="metric-card"><div class="metric-label">Open (Need Contact/In Progress)</div><div class="metric-value" id="vendor-active-count">{{ $stats['activeVendors'] }}</div></div></div>
    <div class="col-md-4"><div class="metric-card"><div class="metric-label">Booked / Done</div><div class="metric-value" id="vendor-done-count">{{ $stats['doneVendors'] }}</div></div></div>
</div>

<div class="planner-card">
    <div class="card-header pt-3 px-3 fw-semibold">Vendor Tracker</div>
    <div class="card-body pt-2">
        <div class="table-responsive">
            <table class="table table-clean table-sm align-middle mb-0" data-sheet-table data-sheet-name="vendors" data-reorder-groups-url="{{ route('vendors.reorder-groups') }}" data-enter-next-field="vendor_name" data-create-url="{{ route('vendors.store') }}" data-bulk-create-url="{{ route('vendors.bulk-store') }}" data-bulk-delete-url="{{ route('vendors.bulk-destroy') }}" data-update-url="/vendors/__ID__" data-delete-url="/vendors/__ID__" data-required="vendor_name,status">
                <thead>
                <tr>
                    <th>Vendor Name</th>
                    <th>Group</th>
                    <th>Contact Name</th>
                    <th>Contact Number</th>
                    <th>Contact Email</th>
                    <th>Website</th>
                    <th>Reference</th>
                    <th>Status</th>
                    <th class="row-actions">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @php
                    $currentGroup = null;
                    $currentGroupKey = null;
                    $groupIndex = 0;
                    $groupCounts = $vendors->groupBy(function ($item) {
                        $label = trim((string) $item->group_name);
                        return $label !== '' ? $label : 'Tanpa Group';
                    })->map(function ($items) {
                        return (int) $items->count();
                    });
                @endphp
                @foreach($vendors as $vendor)
                    @php
                        $groupKey = trim((string) $vendor->group_name);
                        $groupLabel = $groupKey !== '' ? $groupKey : 'Tanpa Group';
                    @endphp
                    @if($groupLabel !== $currentGroup || $groupKey !== $currentGroupKey)
                        @if($groupIndex > 0)
                            <tr class="vendor-group-gap"><td colspan="9"></td></tr>
                        @endif
                        <tr class="vendor-group-separator" data-group-key="{{ $groupKey }}">
                            <td colspan="9">
                                <div class="vendor-group-header">
                                    <div class="vendor-group-title-wrap">
                                        <span class="vendor-group-title">{{ $groupLabel }}</span>
                                        <span class="vendor-group-total vendor-group-total-count">Total Vendor: {{ $groupCounts[$groupLabel] ?? 0 }}</span>
                                    </div>
                                    <div class="vendor-group-actions">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-add-to-group="1" title="Tambah vendor ke group ini">+ Vendor</button>
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
                    <tr data-row data-id="{{ $vendor->id }}">
                        <td>
                            <input type="hidden" class="sheet-cell" data-field="group_sort_order" value="{{ $vendor->group_sort_order }}">
                            <input class="form-control form-control-sm sheet-cell" data-field="vendor_name" value="{{ $vendor->vendor_name }}">
                        </td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="group_name" value="{{ $vendor->group_name }}" placeholder="Nama group"></td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="contact_name" value="{{ $vendor->contact_name }}"></td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="contact_number" data-phone-digits="1" data-phone-display="id" value="{{ $vendor->contact_number }}"></td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="contact_email" value="{{ $vendor->contact_email }}"></td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="website" value="{{ $vendor->website }}"></td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="reference" value="{{ $vendor->reference }}"></td>
                        <td>
                            <select class="form-select form-select-sm sheet-cell" data-field="status">
                                <option value="not_started" {{ $vendor->status === 'not_started' ? 'selected' : '' }}>Need Contact</option>
                                <option value="in_progress" {{ $vendor->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="done" {{ $vendor->status === 'done' ? 'selected' : '' }}>Booked</option>
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
                        <input type="hidden" class="sheet-cell" data-field="group_sort_order" value="0">
                        <input class="form-control form-control-sm sheet-cell" data-field="vendor_name" placeholder="Vendor Name">
                    </td>
                    <td><input class="form-control form-control-sm sheet-cell" data-field="group_name" placeholder="Nama group"></td>
                    <td><input class="form-control form-control-sm sheet-cell" data-field="contact_name" placeholder="Contact Name"></td>
                    <td><input class="form-control form-control-sm sheet-cell" data-field="contact_number" data-phone-digits="1" data-phone-display="id" placeholder="Contact Number"></td>
                    <td><input class="form-control form-control-sm sheet-cell" data-field="contact_email" placeholder="Contact Email"></td>
                    <td><input class="form-control form-control-sm sheet-cell" data-field="website" placeholder="Website"></td>
                    <td><input class="form-control form-control-sm sheet-cell" data-field="reference" placeholder="Reference"></td>
                    <td>
                        <select class="form-select form-select-sm sheet-cell" data-field="status">
                            <option value="not_started" selected>Need Contact</option>
                            <option value="in_progress">In Progress</option>
                            <option value="done">Booked</option>
                        </select>
                    </td>
                    <td class="row-actions"></td>
                </tr>
                </tbody>

                <template data-new-row-template>
                    <tr data-row data-new-row="1" class="inline-add-row">
                        <td>
                            <input type="hidden" class="sheet-cell" data-field="group_sort_order" value="0">
                            <input class="form-control form-control-sm sheet-cell" data-field="vendor_name" placeholder="Vendor Name">
                        </td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="group_name" placeholder="Nama group"></td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="contact_name" placeholder="Contact Name"></td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="contact_number" data-phone-digits="1" data-phone-display="id" placeholder="Contact Number"></td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="contact_email" placeholder="Contact Email"></td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="website" placeholder="Website"></td>
                        <td><input class="form-control form-control-sm sheet-cell" data-field="reference" placeholder="Reference"></td>
                        <td>
                            <select class="form-select form-select-sm sheet-cell" data-field="status">
                                <option value="not_started" selected>Need Contact</option>
                                <option value="in_progress">In Progress</option>
                                <option value="done">Booked</option>
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

        function recalcVendorStats() {
            var rows = document.querySelectorAll('table[data-create-url="{{ route('vendors.store') }}"] tbody tr[data-row][data-id]');
            var totalCount = 0;
            var activeCount = 0;
            var doneCount = 0;

            rows.forEach(function (row) {
                totalCount += 1;
                var statusInput = row.querySelector('[data-field="status"]');
                var status = statusInput ? statusInput.value : 'not_started';
                if (status === 'done') {
                    doneCount += 1;
                } else if (status === 'not_started' || status === 'in_progress') {
                    activeCount += 1;
                }
            });

            var totalEl = document.getElementById('vendor-total-count');
            var activeEl = document.getElementById('vendor-active-count');
            var doneEl = document.getElementById('vendor-done-count');
            if (totalEl) totalEl.textContent = String(totalCount);
            if (activeEl) activeEl.textContent = String(activeCount);
            if (doneEl) doneEl.textContent = String(doneCount);
        }

        function normalizeGroupValue(value) {
            return String(value || '').trim();
        }

        function clearVendorGroupVisualRows(tbody) {
            tbody.querySelectorAll('tr.vendor-group-separator, tr.vendor-group-gap').forEach(function (row) {
                row.remove();
            });
        }

        function buildVendorGroupGapRow() {
            var tr = document.createElement('tr');
            tr.className = 'vendor-group-gap';
            var td = document.createElement('td');
            td.colSpan = 9;
            tr.appendChild(td);
            return tr;
        }

        function buildVendorGroupSeparatorRow(groupKey, label, count) {
            var tr = document.createElement('tr');
            tr.className = 'vendor-group-separator';
            tr.dataset.groupKey = groupKey;
            var td = document.createElement('td');
            td.colSpan = 9;
            td.innerHTML = '' +
                '<div class="vendor-group-header">' +
                    '<div class="vendor-group-title-wrap">' +
                        '<span class="vendor-group-title">' + label + '</span>' +
                        '<span class="vendor-group-total vendor-group-total-count">Total Vendor: ' + count + '</span>' +
                    '</div>' +
                    '<div class="vendor-group-actions">' +
                        '<button type="button" class="btn btn-sm btn-outline-secondary" data-add-to-group="1" title="Tambah vendor ke group ini">+ Vendor</button>' +
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

        function getGroupCountMap(rows) {
            var totals = new Map();
            rows.forEach(function (row) {
                var groupInput = row.querySelector('[data-field="group_name"]');
                var groupKey = normalizeGroupValue(groupInput ? groupInput.value : '');
                totals.set(groupKey, (totals.get(groupKey) || 0) + 1);
            });
            return totals;
        }

        function persistVendorGroupOrder(table) {
            if (!table) return Promise.resolve();
            var url = table.dataset.reorderGroupsUrl;
            if (!url) return Promise.resolve();

            var orderedGroups = [];
            table.querySelectorAll('tr.vendor-group-separator').forEach(function (row) {
                orderedGroups.push(row.dataset.groupKey || '');
            });

            return requestJson(url, 'POST', {
                ordered_groups: orderedGroups
            });
        }

        function regroupVendorRows(table) {
            if (!table) return;
            var tbody = table.querySelector('tbody');
            if (!tbody) return;

            clearVendorGroupVisualRows(tbody);

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

                var aId = parseInt(a.dataset.id || '0', 10);
                var bId = parseInt(b.dataset.id || '0', 10);
                return aId - bId;
            });

            rows.forEach(function (row) {
                tbody.insertBefore(row, addRow || null);
            });

            var groupRowsMap = getGroupRowsMap(rows);
            var groupCountMap = getGroupCountMap(rows);

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
                    tbody.insertBefore(buildVendorGroupGapRow(), groupRows[0]);
                }

                var groupLabel = groupKey || 'Tanpa Group';
                var count = groupCountMap.get(groupKey) || 0;
                tbody.insertBefore(buildVendorGroupSeparatorRow(groupKey, groupLabel, count), groupRows[0]);
            });
        }

        function moveVendorGroup(table, groupKey, direction) {
            if (!table) return;
            var tbody = table.querySelector('tbody');
            if (!tbody) return;

            var separators = Array.from(tbody.querySelectorAll('tr.vendor-group-separator'));
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

            regroupVendorRows(table);
            persistVendorGroupOrder(table).catch(console.error);
        }

        function bindVendorRows() {
            document.querySelectorAll('table[data-sheet-name="vendors"] tbody tr[data-row]').forEach(function (row) {
                if (row.dataset.groupBound === '1') return;
                row.dataset.groupBound = '1';

                var groupInput = row.querySelector('[data-field="group_name"]');
                if (!groupInput) return;

                groupInput.addEventListener('change', function () {
                    var table = row.closest('table[data-sheet-name="vendors"]');
                    if (table) {
                        regroupVendorRows(table);
                    }
                });
            });
        }

        function initVendorGroupReorderControls() {
            document.querySelectorAll('table[data-sheet-name="vendors"]').forEach(function (table) {
                var tbody = table.querySelector('tbody');
                if (!tbody || tbody.dataset.groupMoveBound === '1') return;
                tbody.dataset.groupMoveBound = '1';

                tbody.addEventListener('click', function (event) {
                    var addButton = event.target.closest('[data-add-to-group]');
                    if (addButton) {
                        var addSeparatorRow = addButton.closest('tr.vendor-group-separator');
                        if (!addSeparatorRow) return;
                        event.preventDefault();
                        fillNewVendorRowForGroup(table, addSeparatorRow.dataset.groupKey || '');
                        return;
                    }

                    var button = event.target.closest('[data-move-group]');
                    if (!button) return;

                    var separatorRow = button.closest('tr.vendor-group-separator');
                    if (!separatorRow) return;

                    event.preventDefault();
                    moveVendorGroup(table, separatorRow.dataset.groupKey || '', button.dataset.moveGroup);
                });
            });
        }

        function fillNewVendorRowForGroup(table, groupKey) {
            if (!table) return;
            var tbody = table.querySelector('tbody');
            if (!tbody) return;

            var addRow = tbody.querySelector('tr[data-new-row="1"]');
            if (!addRow) return;

            var nameInput = addRow.querySelector('[data-field="vendor_name"]');
            var groupInput = addRow.querySelector('[data-field="group_name"]');
            var groupSortInput = addRow.querySelector('[data-field="group_sort_order"]');
            if (!nameInput || !groupInput || !groupSortInput) return;

            groupInput.value = groupKey || '';
            groupSortInput.value = String(resolveGroupSortOrderForValue(table, groupKey || ''));

            var separatorRows = Array.from(tbody.querySelectorAll('tr.vendor-group-separator'));
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
                    if (cursor.matches('tr.vendor-group-separator') || cursor.matches('tr.vendor-group-gap')) {
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
                } else if (targetSeparator.nextElementSibling) {
                    tbody.insertBefore(addRow, targetSeparator.nextElementSibling);
                } else {
                    tbody.appendChild(addRow);
                }
            }

            nameInput.focus();
            if (nameInput.select) nameInput.select();
        }

        function recalcVendorGroupTotals(table) {
            if (!table) return;
            var tbody = table.querySelector('tbody');
            if (!tbody) return;

            var rows = Array.from(tbody.querySelectorAll('tr[data-row][data-id]'));
            var groupCountMap = getGroupCountMap(rows);

            tbody.querySelectorAll('tr.vendor-group-separator').forEach(function (separator) {
                var groupKey = separator.dataset.groupKey || '';
                var count = groupCountMap.get(groupKey) || 0;
                var countEl = separator.querySelector('.vendor-group-total-count');
                if (countEl) {
                    countEl.textContent = 'Total Vendor: ' + count;
                }
            });
        }

        document.addEventListener('sheet:changed', function (event) {
            var table = event.detail && event.detail.table;
            if (table && table.dataset.createUrl === "{{ route('vendors.store') }}") {
                bindVendorRows();
                regroupVendorRows(table);
                recalcVendorGroupTotals(table);
                recalcVendorStats();
            }
        });

        document.addEventListener('change', function (event) {
            var target = event.target;
            if (!target) return;
            if (target.dataset.field === 'status' && target.closest('table[data-create-url="{{ route('vendors.store') }}"]')) {
                recalcVendorStats();
            }
        });

        bindVendorRows();
        initVendorGroupReorderControls();
        document.querySelectorAll('table[data-sheet-name="vendors"]').forEach(function (table) {
            regroupVendorRows(table);
            recalcVendorGroupTotals(table);
        });
        recalcVendorStats();
    })();
</script>
<style>
    table[data-sheet-name="vendors"] th {
        padding-top: 0.82rem;
        padding-bottom: 0.82rem;
    }

    table[data-sheet-name="vendors"] td {
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
    }

    .vendor-group-gap td {
        height: 12px;
        padding: 0 !important;
        border: 0 !important;
        background: transparent !important;
    }

    .vendor-group-separator td {
        background: #e7efe6 !important;
        color: #50644f;
        font-weight: 700;
        letter-spacing: 0.25px;
        border-top: 2px solid #c9d8c8;
        border-bottom: 1px solid #d8e4d7;
        padding-top: 0.42rem !important;
        padding-bottom: 0.42rem !important;
    }

    .vendor-group-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .vendor-group-title-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .vendor-group-title {
        font-weight: 700;
    }

    .vendor-group-total {
        font-weight: 600;
        color: #637b62;
        font-size: 0.82rem;
    }

    .vendor-group-actions {
        display: inline-flex;
        gap: 6px;
        flex-shrink: 0;
    }

    .vendor-group-actions .btn {
        padding: 0.1rem 0.42rem;
        line-height: 1;
        font-size: 0.78rem;
    }
</style>
@endpush
