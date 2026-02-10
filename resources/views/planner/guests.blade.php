@extends('layouts.planner')

@section('title', 'Tracker Undangan')
@section('subtitle', 'For Our Special Event')

@section('content')
<ul class="nav planner-nav mb-3" id="guest-subtabs">
    <li class="nav-item"><button class="nav-link active" type="button" data-guest-tab="lamaran">Lamaran</button></li>
    <li class="nav-item"><button class="nav-link" type="button" data-guest-tab="resepsi">Resepsi</button></li>
</ul>

<div data-guest-panel="lamaran">
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="metric-card"><div class="metric-label">Total Undangan</div><div class="metric-value" id="guest-total-lamaran">{{ $stats['lamaran']['totalGuests'] }}</div></div></div>
        <div class="col-md-3"><div class="metric-card"><div class="metric-label">Hadir</div><div class="metric-value" id="guest-attending-lamaran">{{ $stats['lamaran']['attendingGuests'] }}</div></div></div>
        <div class="col-md-3"><div class="metric-card"><div class="metric-label">Tidak Hadir</div><div class="metric-value" id="guest-not-attending-lamaran">{{ $stats['lamaran']['notAttendingGuests'] }}</div></div></div>
        <div class="col-md-3"><div class="metric-card"><div class="metric-label">CPP / CPW</div><div class="metric-value"><span id="guest-cpp-total-lamaran">{{ $stats['lamaran']['cppTotalGuests'] }}</span> / <span id="guest-cpw-total-lamaran">{{ $stats['lamaran']['cpwTotalGuests'] }}</span></div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-6">
            <div class="planner-card h-100">
                <div class="card-header bg-white border-0 pt-3 px-3 fw-semibold">Daftar Undangan CPP - Lamaran</div>
                <div class="card-body pt-2">
                    <div class="table-responsive">
                        <table class="table table-clean table-sm align-middle mb-0" data-sheet-table data-sheet-name="guests" data-side="cpp" data-event-type="lamaran" data-reorder-url="{{ route('guests.reorder') }}" data-enter-next-field="name" data-create-url="{{ route('guests.store') }}" data-update-url="/guests/__ID__" data-delete-url="/guests/__ID__" data-required="name,side,event_type,attendance_status">
                            <thead>
                            <tr><th>Nama</th><th>Kontak</th><th>Notes</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                            @foreach($lamaranCppGuests as $guest)
                                <tr data-row data-id="{{ $guest->id }}">
                                    <td>
                                        <input type="hidden" class="sheet-cell" data-field="side" value="{{ $guest->side }}">
                                        <input type="hidden" class="sheet-cell" data-field="event_type" value="{{ $guest->event_type }}">
                                        <input type="hidden" class="sheet-cell" data-field="sort_order" value="{{ $guest->sort_order }}">
                                        <div class="name-cell">
                                            <span class="drag-handle">::</span>
                                            <input class="form-control form-control-sm sheet-cell" data-field="name" value="{{ $guest->name }}">
                                        </div>
                                    </td>
                                    <td><input class="form-control form-control-sm sheet-cell" data-field="phone" value="{{ $guest->phone }}"></td>
                                    <td><input class="form-control form-control-sm sheet-cell" data-field="notes" value="{{ $guest->notes }}"></td>
                                    <td>
                                        <select class="form-select form-select-sm sheet-cell" data-field="attendance_status">
                                            <option value="invited" {{ $guest->attendance_status === 'invited' ? 'selected' : '' }}>Diundang</option>
                                            <option value="attending" {{ $guest->attendance_status === 'attending' ? 'selected' : '' }}>Hadir</option>
                                            <option value="not_attending" {{ $guest->attendance_status === 'not_attending' ? 'selected' : '' }}>Tidak Hadir</option>
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                            <tr data-row data-new-row="1" class="inline-add-row">
                                <td>
                                    <input type="hidden" class="sheet-cell" data-field="side" value="cpp">
                                    <input type="hidden" class="sheet-cell" data-field="event_type" value="lamaran">
                                    <input type="hidden" class="sheet-cell" data-field="sort_order" value="0">
                                    <input class="form-control form-control-sm sheet-cell" data-field="name" placeholder="Nama">
                                </td>
                                <td><input class="form-control form-control-sm sheet-cell" data-field="phone" placeholder="No. HP"></td>
                                <td><input class="form-control form-control-sm sheet-cell" data-field="notes" placeholder="Catatan"></td>
                                <td><select class="form-select form-select-sm sheet-cell" data-field="attendance_status"><option value="invited" selected>Diundang</option><option value="attending">Hadir</option><option value="not_attending">Tidak Hadir</option></select></td>
                            </tr>
                            </tbody>
                            <template data-new-row-template>
                                <tr data-row data-new-row="1" class="inline-add-row">
                                    <td><input type="hidden" class="sheet-cell" data-field="side" value="cpp"><input type="hidden" class="sheet-cell" data-field="event_type" value="lamaran"><input type="hidden" class="sheet-cell" data-field="sort_order" value="0"><input class="form-control form-control-sm sheet-cell" data-field="name" placeholder="Nama"></td>
                                    <td><input class="form-control form-control-sm sheet-cell" data-field="phone" placeholder="No. HP"></td>
                                    <td><input class="form-control form-control-sm sheet-cell" data-field="notes" placeholder="Catatan"></td>
                                    <td><select class="form-select form-select-sm sheet-cell" data-field="attendance_status"><option value="invited" selected>Diundang</option><option value="attending">Hadir</option><option value="not_attending">Tidak Hadir</option></select></td>
                                </tr>
                            </template>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="planner-card h-100">
                <div class="card-header bg-white border-0 pt-3 px-3 fw-semibold">Daftar Undangan CPW - Lamaran</div>
                <div class="card-body pt-2">
                    <div class="table-responsive">
                        <table class="table table-clean table-sm align-middle mb-0" data-sheet-table data-sheet-name="guests" data-side="cpw" data-event-type="lamaran" data-reorder-url="{{ route('guests.reorder') }}" data-enter-next-field="name" data-create-url="{{ route('guests.store') }}" data-update-url="/guests/__ID__" data-delete-url="/guests/__ID__" data-required="name,side,event_type,attendance_status">
                            <thead>
                            <tr><th>Nama</th><th>Kontak</th><th>Notes</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                            @foreach($lamaranCpwGuests as $guest)
                                <tr data-row data-id="{{ $guest->id }}">
                                    <td>
                                        <input type="hidden" class="sheet-cell" data-field="side" value="{{ $guest->side }}">
                                        <input type="hidden" class="sheet-cell" data-field="event_type" value="{{ $guest->event_type }}">
                                        <input type="hidden" class="sheet-cell" data-field="sort_order" value="{{ $guest->sort_order }}">
                                        <div class="name-cell">
                                            <span class="drag-handle">::</span>
                                            <input class="form-control form-control-sm sheet-cell" data-field="name" value="{{ $guest->name }}">
                                        </div>
                                    </td>
                                    <td><input class="form-control form-control-sm sheet-cell" data-field="phone" value="{{ $guest->phone }}"></td>
                                    <td><input class="form-control form-control-sm sheet-cell" data-field="notes" value="{{ $guest->notes }}"></td>
                                    <td>
                                        <select class="form-select form-select-sm sheet-cell" data-field="attendance_status">
                                            <option value="invited" {{ $guest->attendance_status === 'invited' ? 'selected' : '' }}>Diundang</option>
                                            <option value="attending" {{ $guest->attendance_status === 'attending' ? 'selected' : '' }}>Hadir</option>
                                            <option value="not_attending" {{ $guest->attendance_status === 'not_attending' ? 'selected' : '' }}>Tidak Hadir</option>
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                            <tr data-row data-new-row="1" class="inline-add-row">
                                <td>
                                    <input type="hidden" class="sheet-cell" data-field="side" value="cpw">
                                    <input type="hidden" class="sheet-cell" data-field="event_type" value="lamaran">
                                    <input type="hidden" class="sheet-cell" data-field="sort_order" value="0">
                                    <input class="form-control form-control-sm sheet-cell" data-field="name" placeholder="Nama">
                                </td>
                                <td><input class="form-control form-control-sm sheet-cell" data-field="phone" placeholder="No. HP"></td>
                                <td><input class="form-control form-control-sm sheet-cell" data-field="notes" placeholder="Catatan"></td>
                                <td><select class="form-select form-select-sm sheet-cell" data-field="attendance_status"><option value="invited" selected>Diundang</option><option value="attending">Hadir</option><option value="not_attending">Tidak Hadir</option></select></td>
                            </tr>
                            </tbody>
                            <template data-new-row-template>
                                <tr data-row data-new-row="1" class="inline-add-row">
                                    <td><input type="hidden" class="sheet-cell" data-field="side" value="cpw"><input type="hidden" class="sheet-cell" data-field="event_type" value="lamaran"><input type="hidden" class="sheet-cell" data-field="sort_order" value="0"><input class="form-control form-control-sm sheet-cell" data-field="name" placeholder="Nama"></td>
                                    <td><input class="form-control form-control-sm sheet-cell" data-field="phone" placeholder="No. HP"></td>
                                    <td><input class="form-control form-control-sm sheet-cell" data-field="notes" placeholder="Catatan"></td>
                                    <td><select class="form-select form-select-sm sheet-cell" data-field="attendance_status"><option value="invited" selected>Diundang</option><option value="attending">Hadir</option><option value="not_attending">Tidak Hadir</option></select></td>
                                </tr>
                            </template>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div data-guest-panel="resepsi" style="display:none;">
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="metric-card"><div class="metric-label">Total Undangan</div><div class="metric-value" id="guest-total-resepsi">{{ $stats['resepsi']['totalGuests'] }}</div></div></div>
        <div class="col-md-3"><div class="metric-card"><div class="metric-label">Hadir</div><div class="metric-value" id="guest-attending-resepsi">{{ $stats['resepsi']['attendingGuests'] }}</div></div></div>
        <div class="col-md-3"><div class="metric-card"><div class="metric-label">Tidak Hadir</div><div class="metric-value" id="guest-not-attending-resepsi">{{ $stats['resepsi']['notAttendingGuests'] }}</div></div></div>
        <div class="col-md-3"><div class="metric-card"><div class="metric-label">CPP / CPW</div><div class="metric-value"><span id="guest-cpp-total-resepsi">{{ $stats['resepsi']['cppTotalGuests'] }}</span> / <span id="guest-cpw-total-resepsi">{{ $stats['resepsi']['cpwTotalGuests'] }}</span></div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-6">
            <div class="planner-card h-100">
                <div class="card-header bg-white border-0 pt-3 px-3 fw-semibold">Daftar Undangan CPP - Resepsi</div>
                <div class="card-body pt-2">
                    <div class="table-responsive">
                        <table class="table table-clean table-sm align-middle mb-0" data-sheet-table data-sheet-name="guests" data-side="cpp" data-event-type="resepsi" data-reorder-url="{{ route('guests.reorder') }}" data-enter-next-field="name" data-create-url="{{ route('guests.store') }}" data-update-url="/guests/__ID__" data-delete-url="/guests/__ID__" data-required="name,side,event_type,attendance_status">
                            <thead>
                            <tr><th>Nama</th><th>Kontak</th><th>Notes</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                            @foreach($resepsiCppGuests as $guest)
                                <tr data-row data-id="{{ $guest->id }}">
                                    <td>
                                        <input type="hidden" class="sheet-cell" data-field="side" value="{{ $guest->side }}">
                                        <input type="hidden" class="sheet-cell" data-field="event_type" value="{{ $guest->event_type }}">
                                        <input type="hidden" class="sheet-cell" data-field="sort_order" value="{{ $guest->sort_order }}">
                                        <div class="name-cell">
                                            <span class="drag-handle">::</span>
                                            <input class="form-control form-control-sm sheet-cell" data-field="name" value="{{ $guest->name }}">
                                        </div>
                                    </td>
                                    <td><input class="form-control form-control-sm sheet-cell" data-field="phone" value="{{ $guest->phone }}"></td>
                                    <td><input class="form-control form-control-sm sheet-cell" data-field="notes" value="{{ $guest->notes }}"></td>
                                    <td>
                                        <select class="form-select form-select-sm sheet-cell" data-field="attendance_status">
                                            <option value="invited" {{ $guest->attendance_status === 'invited' ? 'selected' : '' }}>Diundang</option>
                                            <option value="attending" {{ $guest->attendance_status === 'attending' ? 'selected' : '' }}>Hadir</option>
                                            <option value="not_attending" {{ $guest->attendance_status === 'not_attending' ? 'selected' : '' }}>Tidak Hadir</option>
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                            <tr data-row data-new-row="1" class="inline-add-row">
                                <td>
                                    <input type="hidden" class="sheet-cell" data-field="side" value="cpp">
                                    <input type="hidden" class="sheet-cell" data-field="event_type" value="resepsi">
                                    <input type="hidden" class="sheet-cell" data-field="sort_order" value="0">
                                    <input class="form-control form-control-sm sheet-cell" data-field="name" placeholder="Nama">
                                </td>
                                <td><input class="form-control form-control-sm sheet-cell" data-field="phone" placeholder="No. HP"></td>
                                <td><input class="form-control form-control-sm sheet-cell" data-field="notes" placeholder="Catatan"></td>
                                <td><select class="form-select form-select-sm sheet-cell" data-field="attendance_status"><option value="invited" selected>Diundang</option><option value="attending">Hadir</option><option value="not_attending">Tidak Hadir</option></select></td>
                            </tr>
                            </tbody>
                            <template data-new-row-template>
                                <tr data-row data-new-row="1" class="inline-add-row">
                                    <td><input type="hidden" class="sheet-cell" data-field="side" value="cpp"><input type="hidden" class="sheet-cell" data-field="event_type" value="resepsi"><input type="hidden" class="sheet-cell" data-field="sort_order" value="0"><input class="form-control form-control-sm sheet-cell" data-field="name" placeholder="Nama"></td>
                                    <td><input class="form-control form-control-sm sheet-cell" data-field="phone" placeholder="No. HP"></td>
                                    <td><input class="form-control form-control-sm sheet-cell" data-field="notes" placeholder="Catatan"></td>
                                    <td><select class="form-select form-select-sm sheet-cell" data-field="attendance_status"><option value="invited" selected>Diundang</option><option value="attending">Hadir</option><option value="not_attending">Tidak Hadir</option></select></td>
                                </tr>
                            </template>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="planner-card h-100">
                <div class="card-header bg-white border-0 pt-3 px-3 fw-semibold">Daftar Undangan CPW - Resepsi</div>
                <div class="card-body pt-2">
                    <div class="table-responsive">
                        <table class="table table-clean table-sm align-middle mb-0" data-sheet-table data-sheet-name="guests" data-side="cpw" data-event-type="resepsi" data-reorder-url="{{ route('guests.reorder') }}" data-enter-next-field="name" data-create-url="{{ route('guests.store') }}" data-update-url="/guests/__ID__" data-delete-url="/guests/__ID__" data-required="name,side,event_type,attendance_status">
                            <thead>
                            <tr><th>Nama</th><th>Kontak</th><th>Notes</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                            @foreach($resepsiCpwGuests as $guest)
                                <tr data-row data-id="{{ $guest->id }}">
                                    <td>
                                        <input type="hidden" class="sheet-cell" data-field="side" value="{{ $guest->side }}">
                                        <input type="hidden" class="sheet-cell" data-field="event_type" value="{{ $guest->event_type }}">
                                        <input type="hidden" class="sheet-cell" data-field="sort_order" value="{{ $guest->sort_order }}">
                                        <div class="name-cell">
                                            <span class="drag-handle">::</span>
                                            <input class="form-control form-control-sm sheet-cell" data-field="name" value="{{ $guest->name }}">
                                        </div>
                                    </td>
                                    <td><input class="form-control form-control-sm sheet-cell" data-field="phone" value="{{ $guest->phone }}"></td>
                                    <td><input class="form-control form-control-sm sheet-cell" data-field="notes" value="{{ $guest->notes }}"></td>
                                    <td>
                                        <select class="form-select form-select-sm sheet-cell" data-field="attendance_status">
                                            <option value="invited" {{ $guest->attendance_status === 'invited' ? 'selected' : '' }}>Diundang</option>
                                            <option value="attending" {{ $guest->attendance_status === 'attending' ? 'selected' : '' }}>Hadir</option>
                                            <option value="not_attending" {{ $guest->attendance_status === 'not_attending' ? 'selected' : '' }}>Tidak Hadir</option>
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                            <tr data-row data-new-row="1" class="inline-add-row">
                                <td>
                                    <input type="hidden" class="sheet-cell" data-field="side" value="cpw">
                                    <input type="hidden" class="sheet-cell" data-field="event_type" value="resepsi">
                                    <input type="hidden" class="sheet-cell" data-field="sort_order" value="0">
                                    <input class="form-control form-control-sm sheet-cell" data-field="name" placeholder="Nama">
                                </td>
                                <td><input class="form-control form-control-sm sheet-cell" data-field="phone" placeholder="No. HP"></td>
                                <td><input class="form-control form-control-sm sheet-cell" data-field="notes" placeholder="Catatan"></td>
                                <td><select class="form-select form-select-sm sheet-cell" data-field="attendance_status"><option value="invited" selected>Diundang</option><option value="attending">Hadir</option><option value="not_attending">Tidak Hadir</option></select></td>
                            </tr>
                            </tbody>
                            <template data-new-row-template>
                                <tr data-row data-new-row="1" class="inline-add-row">
                                    <td><input type="hidden" class="sheet-cell" data-field="side" value="cpw"><input type="hidden" class="sheet-cell" data-field="event_type" value="resepsi"><input type="hidden" class="sheet-cell" data-field="sort_order" value="0"><input class="form-control form-control-sm sheet-cell" data-field="name" placeholder="Nama"></td>
                                    <td><input class="form-control form-control-sm sheet-cell" data-field="phone" placeholder="No. HP"></td>
                                    <td><input class="form-control form-control-sm sheet-cell" data-field="notes" placeholder="Catatan"></td>
                                    <td><select class="form-select form-select-sm sheet-cell" data-field="attendance_status"><option value="invited" selected>Diundang</option><option value="attending">Hadir</option><option value="not_attending">Tidak Hadir</option></select></td>
                                </tr>
                            </template>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-scripts')
<script>
    (function () {
        let draggingRow = null;
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const clientIdValue = (window.__clientId || '');

        async function requestJson(url, method, payload) {
            const response = await fetch(url, {
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

        function switchGuestTab(tab) {
            document.querySelectorAll('[data-guest-tab]').forEach(function (btn) {
                btn.classList.toggle('active', btn.dataset.guestTab === tab);
            });
            document.querySelectorAll('[data-guest-panel]').forEach(function (panel) {
                panel.style.display = panel.dataset.guestPanel === tab ? '' : 'none';
            });
        }

        function refreshGuestDraggableRows() {
            document.querySelectorAll('table[data-sheet-name="guests"] tr[data-row][data-id]').forEach(function (row) {
                row.setAttribute('draggable', 'true');
            });
        }

        function ensureGuestRowDecorations() {
            document.querySelectorAll('table[data-sheet-name="guests"] tr[data-row][data-id]').forEach(function (row) {
                const nameInput = row.querySelector('input[data-field="name"]:not([type="hidden"])');
                if (!nameInput) return;
                if (nameInput.closest('.name-cell')) return;

                const wrapper = document.createElement('div');
                wrapper.className = 'name-cell';

                const handle = document.createElement('span');
                handle.className = 'drag-handle';
                handle.textContent = '::';

                nameInput.parentNode.insertBefore(wrapper, nameInput);
                wrapper.appendChild(handle);
                wrapper.appendChild(nameInput);
            });
        }

        function getDropBeforeRow(tbody, pointerY) {
            const rows = Array.from(tbody.querySelectorAll('tr[data-row][data-id]')).filter(function (row) {
                return row !== draggingRow;
            });

            for (let i = 0; i < rows.length; i++) {
                const box = rows[i].getBoundingClientRect();
                if (pointerY < box.top + (box.height / 2)) {
                    return rows[i];
                }
            }

            return null;
        }

        async function persistGuestOrder(table) {
            if (!table || !table.dataset.reorderUrl) return;
            const ids = Array.from(table.querySelectorAll('tbody tr[data-row][data-id]')).map(function (row) {
                return Number(row.dataset.id);
            });
            if (!ids.length) return;

            await requestJson(table.dataset.reorderUrl, 'POST', {
                event_type: table.dataset.eventType,
                side: table.dataset.side,
                ordered_ids: ids
            });
        }

        function initGuestDragDrop() {
            document.querySelectorAll('table[data-sheet-name="guests"]').forEach(function (table) {
                const tbody = table.querySelector('tbody');
                if (!tbody || tbody.dataset.dragBound === '1') return;
                tbody.dataset.dragBound = '1';

                tbody.addEventListener('dragstart', function (event) {
                    const row = event.target.closest('tr[data-row][data-id]');
                    if (!row) return;
                    draggingRow = row;
                    row.classList.add('row-dragging');
                    if (event.dataTransfer) event.dataTransfer.effectAllowed = 'move';
                });

                tbody.addEventListener('dragend', function () {
                    document.querySelectorAll('.sheet-drop-target').forEach(function (el) {
                        el.classList.remove('sheet-drop-target');
                    });
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

                    const targetTable = tbody.closest('table[data-sheet-name="guests"]');
                    const sourceTable = draggingRow.closest('table[data-sheet-name="guests"]');
                    if (!targetTable || !sourceTable) return;
                    if ((targetTable.dataset.eventType || '') !== (sourceTable.dataset.eventType || '')) return;

                    const dropBeforeRow = getDropBeforeRow(tbody, event.clientY);
                    const addRow = tbody.querySelector('tr[data-new-row="1"]');
                    if (dropBeforeRow) {
                        tbody.insertBefore(draggingRow, dropBeforeRow);
                    } else {
                        tbody.insertBefore(draggingRow, addRow || null);
                    }

                    const targetSide = targetTable.dataset.side || 'cpp';
                    const targetEventType = targetTable.dataset.eventType || 'lamaran';
                    const sideInput = draggingRow.querySelector('[data-field="side"]');
                    const eventTypeInput = draggingRow.querySelector('[data-field="event_type"]');
                    const sideChanged = sideInput && sideInput.value !== targetSide;
                    const eventTypeChanged = eventTypeInput && eventTypeInput.value !== targetEventType;

                    if (sideInput) sideInput.value = targetSide;
                    if (eventTypeInput) eventTypeInput.value = targetEventType;
                    if (sideChanged || eventTypeChanged) {
                        if (sideInput) sideInput.dispatchEvent(new Event('change', { bubbles: true }));
                    }

                    persistGuestOrder(targetTable).catch(console.error);
                    if (sourceTable !== targetTable) {
                        persistGuestOrder(sourceTable).catch(console.error);
                    }
                });
            });
        }

        function recalcGuestStats() {
            ['lamaran', 'resepsi'].forEach(function (eventType) {
                const rows = document.querySelectorAll('table[data-sheet-name="guests"][data-event-type="' + eventType + '"] tbody tr[data-row][data-id]');
                let total = 0;
                let attending = 0;
                let notAttending = 0;
                let cpp = 0;
                let cpw = 0;

                rows.forEach(function (row) {
                    total += 1;
                    const statusInput = row.querySelector('[data-field="attendance_status"]');
                    const status = statusInput ? statusInput.value : 'invited';
                    if (status === 'attending') attending += 1;
                    if (status === 'not_attending') notAttending += 1;

                    const sideInput = row.querySelector('[data-field="side"]');
                    const side = sideInput ? sideInput.value : 'cpp';
                    if (side === 'cpp') cpp += 1;
                    if (side === 'cpw') cpw += 1;
                });

                const totalEl = document.getElementById('guest-total-' + eventType);
                const attendingEl = document.getElementById('guest-attending-' + eventType);
                const notAttendingEl = document.getElementById('guest-not-attending-' + eventType);
                const cppEl = document.getElementById('guest-cpp-total-' + eventType);
                const cpwEl = document.getElementById('guest-cpw-total-' + eventType);

                if (totalEl) totalEl.textContent = total;
                if (attendingEl) attendingEl.textContent = attending;
                if (notAttendingEl) notAttendingEl.textContent = notAttending;
                if (cppEl) cppEl.textContent = cpp;
                if (cpwEl) cpwEl.textContent = cpw;
            });
        }

        document.querySelectorAll('[data-guest-tab]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                switchGuestTab(btn.dataset.guestTab);
            });
        });

        document.addEventListener('sheet:changed', function (event) {
            const table = event.detail && event.detail.table;
            if (table && table.dataset.sheetName === 'guests') {
                ensureGuestRowDecorations();
                recalcGuestStats();
                refreshGuestDraggableRows();
            }
        });

        document.addEventListener('change', function (event) {
            if (event.target.closest('table[data-sheet-name="guests"]')) {
                recalcGuestStats();
            }
        });

        switchGuestTab('lamaran');
        ensureGuestRowDecorations();
        initGuestDragDrop();
        refreshGuestDraggableRows();
        recalcGuestStats();
    })();
</script>
@endpush
