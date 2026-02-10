<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Our Plan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Sora:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --line: #e4dbd0;
            --text: #2c2420;
            --muted: #8a7f75;
            --accent: #b8956a;
            --accent-rose: #c4848a;
            --accent-soft: #fef8f5;
            --surface: #fffefb;
            --cream: #faf6f1;
            --linen: #f0eae2;
        }

        * { scrollbar-width: thin; scrollbar-color: var(--line) transparent; }
        ::selection { background: rgba(184,149,106,0.2); color: var(--text); }

        body {
            font-family: "Plus Jakarta Sans", "Segoe UI", sans-serif;
            background: var(--cream);
            background-image:
                radial-gradient(ellipse 1200px 500px at 10% -20%, rgba(196,132,138,0.06) 0%, transparent 65%),
                radial-gradient(ellipse 900px 400px at 90% -10%, rgba(184,149,106,0.08) 0%, transparent 60%);
            color: var(--text);
            min-height: 100vh;
        }

        .planner-wrap {
            width: 100%;
            max-width: none;
            animation: fadeInUp 0.45s ease-out;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .planner-card {
            border: 1px solid var(--line);
            border-radius: 16px;
            background: var(--surface);
            box-shadow: 0 4px 24px rgba(44,36,32,0.04);
            overflow: hidden;
        }

        .planner-card .card-header {
            border-bottom: 1px solid var(--line) !important;
            border-top: 0 !important;
            border-left: 0 !important;
            border-right: 0 !important;
            padding: 1rem 1.25rem !important;
            background: var(--surface) !important;
        }

        .planner-title { font-weight: 700; letter-spacing: 0.2px; }
        .planner-subtitle { color: var(--muted); font-size: 0.95rem; }

        .planner-title-wrap {
            text-align: center;
        }

        .planner-title-wrap .planner-title {
            font-family: "Cormorant Garamond", "Georgia", serif;
            font-size: clamp(2rem, 3.5vw, 2.8rem);
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        .planner-title-wrap .planner-subtitle {
            font-family: "Cormorant Garamond", "Georgia", serif;
            font-size: 1.1rem;
            font-style: italic;
            letter-spacing: 0.04em;
        }

        .header-ornament {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            margin: 10px auto 0;
            max-width: 200px;
        }

        .header-ornament::before,
        .header-ornament::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--accent), transparent);
        }

        .header-ornament .ornament-diamond {
            width: 6px;
            height: 6px;
            background: var(--accent);
            transform: rotate(45deg);
            flex-shrink: 0;
        }

        .planner-nav {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 50px;
            padding: 0.35rem;
            display: inline-flex;
            gap: 0.25rem;
        }

        .planner-nav .nav-link {
            color: var(--muted);
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.88rem;
            padding: 0.45rem 1rem;
            transition: all 0.2s ease;
        }

        .planner-nav .nav-link:hover {
            color: var(--text);
            background: var(--linen);
        }

        .planner-nav .nav-link.active {
            background: var(--accent-rose);
            color: #fff;
        }

        .planner-main-nav {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 60px;
            padding: 0.4rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            position: sticky;
            top: 10px;
            z-index: 40;
            backdrop-filter: blur(6px);
            box-shadow: 0 6px 18px rgba(44,36,32,0.08);
            transition: transform 0.25s ease, opacity 0.25s ease;
        }

        .planner-main-nav.sticky-hidden {
            transform: translateY(-140%);
            opacity: 0;
            pointer-events: none;
        }

        .planner-main-nav .nav-item {
            flex: 1;
        }

        .planner-main-nav .nav-link {
            display: block;
            text-align: center;
            border: none;
            background: transparent;
            color: var(--muted);
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.88rem;
            padding: 0.65rem 1rem;
            transition: all 0.25s ease;
            white-space: nowrap;
        }

        .planner-main-nav .nav-link:hover {
            color: var(--text);
            background: var(--linen);
        }

        .planner-main-nav .nav-link.active {
            background: var(--accent);
            color: #fff;
            box-shadow: 0 2px 12px rgba(184,149,106,0.3);
        }

        .metric-card {
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 1rem 1.15rem;
            background: var(--surface);
            border-left: 3px solid var(--accent);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .metric-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(44,36,32,0.06);
        }

        .metric-label {
            font-size: 0.72rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 600;
            margin-bottom: 0.3rem;
        }

        .metric-value {
            font-family: "Sora", "Plus Jakarta Sans", sans-serif;
            font-size: 1.45rem;
            font-weight: 700;
            line-height: 1.1;
            color: var(--text);
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-clean {
            width: 100%;
            table-layout: fixed;
            min-width: 0;
        }

        .table-clean th {
            border-bottom: 2px solid var(--accent);
            color: var(--muted);
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: var(--surface);
            text-align: center;
            vertical-align: middle;
            line-height: 1.25;
            padding: 0.7rem 0.6rem;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .table-clean td {
            vertical-align: middle;
            border-color: var(--line);
            border-top-width: 1px;
            border-bottom-width: 1px;
            padding: 0.45rem 0.5rem;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .table-clean > :not(caption) > * > * {
            border-left: 1px solid #ece5dc;
        }

        .table-clean > :not(caption) > * > *:first-child {
            border-left: 0;
        }

        .table-clean tbody tr:nth-child(odd):not(.inline-add-row) td {
            background: var(--surface);
        }

        .table-clean tbody tr:nth-child(even):not(.inline-add-row) td {
            background: var(--cream);
        }

        .table-clean tbody tr:not(.inline-add-row):hover td {
            background: #efe7dc;
        }

        .table-clean tbody tr[data-row] {
            transition: transform 0.16s ease, background-color 0.15s ease;
        }

        .table-clean tbody tr.row-selected td {
            background: #e8f1ff !important;
            box-shadow: inset 0 0 0 1px #9cb9e7;
        }

        .table-clean tbody tr.row-swipe-armed td {
            background: #fde4e4 !important;
        }

        .sheet-cell {
            width: 100%;
            min-width: 0;
            border: 1px solid transparent;
            border-radius: 6px;
            transition: border-color 0.2s ease, background-color 0.2s ease, box-shadow 0.2s ease;
            background-color: transparent !important;
        }

        .sheet-cell:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 2px rgba(184,149,106,0.15);
            background-color: #fefcf9 !important;
        }

        .sheet-cell::placeholder {
            color: #b0a89e;
        }

        textarea.sheet-cell {
            resize: none;
            overflow: hidden;
            white-space: pre-wrap;
            word-break: break-word;
            line-height: 1.25;
            min-height: 31px;
        }

        .name-cell textarea.sheet-cell {
            flex: 1 1 auto;
        }

        .sheet-tone-invited,
        .sheet-tone-pending,
        .sheet-tone-budget,
        .sheet-tone-not_started {
            background-color: #fdf3e0 !important;
            color: #7a5c1f !important;
            font-weight: 600;
        }

        .sheet-tone-attending,
        .sheet-tone-done,
        .sheet-tone-arrived,
        .sheet-tone-complete {
            background-color: #e8f5e9 !important;
            color: #2e6e3f !important;
            font-weight: 600;
        }

        .sheet-tone-in_progress,
        .sheet-tone-ordered,
        .sheet-tone-on_delivery {
            background-color: #fef0ec !important;
            color: #984e3a !important;
            font-weight: 600;
        }

        .sheet-tone-not_attending,
        .sheet-tone-expense {
            background-color: #fce4ec !important;
            color: #883344 !important;
            font-weight: 600;
        }

        .inline-add-row td {
            background: #fdfbf8;
            border-top: 1px dashed var(--line);
            border-bottom: 1px dashed var(--line);
        }

        tr[data-row][data-id][draggable="true"] {
            cursor: grab;
        }

        tr[data-row][data-id].row-dragging {
            opacity: 0.55;
        }

        .sheet-drop-target {
            outline: 2px dashed var(--accent);
            outline-offset: -2px;
            background: var(--accent-soft);
        }

        .name-cell {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .drag-handle {
            color: #b5aa9e;
            font-weight: 700;
            letter-spacing: 0.5px;
            user-select: none;
            cursor: grab;
            line-height: 1;
            flex: 0 0 auto;
        }

        .row-actions {
            width: 56px;
            text-align: right;
        }

        .row-menu {
            position: relative;
            display: inline-block;
        }

        .row-menu summary {
            list-style: none;
            cursor: pointer;
            border: 1px solid var(--line);
            border-radius: 8px;
            width: 34px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--muted);
            user-select: none;
            background: var(--surface);
            transition: border-color 0.15s ease;
        }

        .row-menu summary:hover {
            border-color: var(--accent);
        }

        .row-menu summary::-webkit-details-marker { display: none; }

        .row-menu[open] .row-menu-panel {
            display: block;
        }

        .row-menu-panel {
            display: none;
            position: absolute;
            right: 0;
            top: 34px;
            z-index: 30;
            min-width: 120px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: var(--surface);
            box-shadow: 0 8px 24px rgba(44,36,32,0.12);
            padding: 6px;
        }

        .row-menu-panel button {
            width: 100%;
            text-align: left;
            border: 0;
            background: var(--surface);
            border-radius: 6px;
            color: var(--accent-rose);
            font-weight: 600;
            padding: 6px 10px;
            transition: background 0.15s ease;
        }

        .row-menu-panel button:hover { background: #fdf2f2; }

        .autosave-hint {
            font-size: 0.8rem;
            color: var(--muted);
            text-align: center;
        }

        .bulk-delete-btn {
            margin-left: 10px;
            border: 1px solid #d8a6a6;
            color: #8b2f2f;
            background: #fff5f5;
            border-radius: 8px;
            font-size: 0.74rem;
            font-weight: 700;
            padding: 4px 10px;
            line-height: 1.2;
        }

        .bulk-delete-btn:hover {
            background: #ffeaea;
        }

        .saving-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 99px;
            margin-right: 6px;
            background: var(--accent);
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .saving .saving-dot {
            opacity: 1;
            animation: savePulse 1s ease-in-out infinite;
        }

        @keyframes savePulse {
            0%, 100% { opacity: 0.4; }
            50% { opacity: 1; }
        }

        @media (max-width: 991.98px) {
            .planner-wrap {
                padding-left: 12px;
                padding-right: 12px;
            }

            .planner-nav {
                display: flex;
                width: 100%;
                overflow-x: auto;
                flex-wrap: nowrap;
                white-space: nowrap;
                scrollbar-width: thin;
            }

            .planner-nav .nav-link {
                padding: 0.4rem 0.7rem;
                font-size: 0.86rem;
            }

            .planner-main-nav {
                flex-wrap: wrap;
                border-radius: 16px;
                max-width: none;
            }

            .planner-main-nav .nav-item {
                flex: 1 1 calc(50% - 0.25rem);
            }

            .planner-main-nav .nav-link {
                border-radius: 12px;
                padding: 0.6rem 0.5rem;
                font-size: 0.84rem;
            }

            .metric-value {
                font-size: 1.15rem;
            }

            .table-clean { min-width: 700px; }
        }

        @media (max-width: 575.98px) {
            .planner-wrap {
                padding-top: 14px !important;
                padding-bottom: 20px !important;
            }

            .planner-title-wrap .planner-title {
                font-size: 1.6rem;
            }

            .planner-title-wrap .planner-subtitle {
                font-size: 0.92rem;
            }

            .autosave-hint {
                font-size: 0.74rem;
            }

            .table-clean th {
                font-size: 0.72rem;
                letter-spacing: 0.2px;
                padding: 0.5rem 0.4rem;
            }

            .table-clean td {
                padding: 0.35rem 0.35rem;
            }

            .form-control.form-control-sm,
            .form-select.form-select-sm {
                font-size: 0.78rem;
                padding: 0.28rem 0.4rem;
            }

            .row-actions {
                width: 42px;
            }

            .row-menu summary {
                width: 30px;
                height: 28px;
            }

            .table-clean { min-width: 640px; }

            .planner-main-nav {
                border-radius: 14px;
            }

            .planner-main-nav .nav-link {
                font-size: 0.8rem;
                padding: 0.55rem 0.4rem;
            }

            .header-ornament {
                margin-top: 6px;
            }
        }
    </style>
</head>
<body>
<div class="container-fluid planner-wrap py-4 py-md-5">
    <div class="planner-title-wrap mb-4">
        <h1 class="planner-title mb-0">Our Plan</h1>
        <div class="planner-subtitle">For our Special Event</div>
        <div class="header-ornament"><span class="ornament-diamond"></span></div>
    </div>

    <ul class="nav planner-main-nav mb-4">
        <li class="nav-item"><a class="nav-link @if(request()->routeIs('guests.*')) active @endif" href="{{ route('guests.index') }}">Undangan</a></li>
        <li class="nav-item"><a class="nav-link @if(request()->routeIs('tasks.*')) active @endif" href="{{ route('tasks.index') }}">To-do</a></li>
        <li class="nav-item"><a class="nav-link @if(request()->routeIs('gifts.*')) active @endif" href="{{ route('gifts.index') }}">Seserahan</a></li>
        <li class="nav-item"><a class="nav-link @if(request()->routeIs('expenses.*')) active @endif" href="{{ route('expenses.index') }}">Budget & Expense</a></li>
    </ul>

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm">
            <strong>Validasi gagal:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="autosave-hint mb-3"><span class="saving-dot"></span>Autosave aktif: Enter untuk lanjut ke row berikutnya, pindah field untuk simpan, Shift+Delete untuk hapus row.
        <button type="button" class="bulk-delete-btn" id="bulk-delete-selected" hidden>Hapus Terpilih (0)</button>
    </div>

    @yield('content')
</div>

<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const hint = document.querySelector('.autosave-hint');
    const bulkDeleteButton = document.getElementById('bulk-delete-selected');
    var clientId = Date.now().toString(36) + Math.random().toString(36).substr(2, 9);
    window.__clientId = clientId;
    const tableConfigs = new WeakMap();
    const selectedRows = new Set();
    let lastSelectedRow = null;

    function setSaving(state) {
        if (!hint) return;
        hint.classList.toggle('saving', state);
    }

    function normalizeValue(input) {
        const raw = (input.value || '').trim();
        if (input.dataset.currencyIdr === '1') {
            if (raw === '') return null;
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
            return Number.isFinite(parsed) ? String(parsed) : null;
        }
        return raw === '' ? null : raw;
    }

    function collectRow(row) {
        const data = {};
        row.querySelectorAll('[data-field]').forEach(function (input) {
            data[input.dataset.field] = normalizeValue(input);
        });
        return data;
    }

    function hasRequiredValues(data, requiredFields) {
        return requiredFields.every(function (field) {
            const value = data[field];
            return value !== null && value !== '';
        });
    }

    function encodeSnapshot(data) {
        return JSON.stringify(data);
    }

    async function requestJson(url, method, payload) {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
                'X-Client-ID': clientId
            },
            body: payload ? JSON.stringify(payload) : null
        });

        if (!response.ok) {
            throw new Error('Request gagal');
        }

        return response.json();
    }

    function closeMenus() {
        document.querySelectorAll('.row-menu[open]').forEach(function (menu) {
            menu.removeAttribute('open');
        });
    }

    function rowSelectionKey(row) {
        if (!row || !row.dataset.id) return '';
        const table = row.closest('[data-sheet-table]');
        const tableKey = table ? (table.dataset.createUrl || table.dataset.updateUrl || '') : '';
        return tableKey + '::' + row.dataset.id;
    }

    function updateBulkDeleteUI() {
        if (!bulkDeleteButton) return;
        const count = selectedRows.size;
        bulkDeleteButton.hidden = count === 0;
        bulkDeleteButton.textContent = 'Hapus Terpilih (' + count + ')';
    }

    function clearSelectedRows(preserveAnchor) {
        selectedRows.forEach(function (key) {
            const row = document.querySelector('tr[data-row][data-id][data-select-key="' + key + '"]');
            if (row) row.classList.remove('row-selected');
        });
        selectedRows.clear();
        if (!preserveAnchor) {
            lastSelectedRow = null;
        }
        updateBulkDeleteUI();
    }

    function selectRow(row, selected) {
        const key = rowSelectionKey(row);
        if (!key) return;
        row.dataset.selectKey = key;
        if (selected) {
            selectedRows.add(key);
            row.classList.add('row-selected');
        } else {
            selectedRows.delete(key);
            row.classList.remove('row-selected');
        }
    }

    function toggleRowSelection(row) {
        const key = rowSelectionKey(row);
        if (!key) return;
        const selected = selectedRows.has(key);
        selectRow(row, !selected);
        lastSelectedRow = row;
        updateBulkDeleteUI();
    }

    function selectRangeRows(anchorRow, targetRow) {
        if (!anchorRow || !targetRow) return;
        const table = targetRow.closest('[data-sheet-table]');
        if (!table) return;
        if (anchorRow.closest('[data-sheet-table]') !== table) return;

        const rows = Array.from(table.querySelectorAll('tbody tr[data-row][data-id]'));
        const start = rows.indexOf(anchorRow);
        const end = rows.indexOf(targetRow);
        if (start < 0 || end < 0) return;

        const from = Math.min(start, end);
        const to = Math.max(start, end);
        for (let i = from; i <= to; i++) {
            selectRow(rows[i], true);
        }
        lastSelectedRow = targetRow;
        updateBulkDeleteUI();
    }

    function shouldHandleRowSelectionClick(event) {
        if (!event || !event.target) return false;
        return !event.target.closest('input, select, textarea, button, a, details, summary, [data-open-link], [data-delete-row]');
    }

    function emitSheetChanged(table) {
        document.dispatchEvent(new CustomEvent('sheet:changed', {
            detail: { table: table }
        }));
    }

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.row-menu')) {
            closeMenus();
        }
    });

    function buildNewRow(table) {
        const template = table.querySelector('template[data-new-row-template]');
        if (!template) return null;
        return template.content.firstElementChild.cloneNode(true);
    }

    function focusNextRowCell(table, row, input) {
        const fallbackField = input.dataset.field;
        const preferredField = row.dataset.newRow === '1'
            ? (table.dataset.enterNextField || fallbackField)
            : fallbackField;
        if (!preferredField) return;

        let nextRow = row.nextElementSibling;
        while (nextRow && !nextRow.matches('tr[data-row]')) {
            nextRow = nextRow.nextElementSibling;
        }
        if (!nextRow) return;

        let nextCellInput = nextRow.querySelector('[data-field=\"' + preferredField + '\"]');
        if (!nextCellInput) {
            nextCellInput = nextRow.querySelector('[data-field]');
        }
        if (!nextCellInput) return;

        nextCellInput.focus();
        if (nextCellInput.select && nextCellInput.tagName === 'INPUT') {
            nextCellInput.select();
        }
    }

    function focusPreferredCell(row, preferredField) {
        if (!row) return;
        let input = row.querySelector('[data-field=\"' + preferredField + '\"]');
        if (!input) {
            input = row.querySelector('[data-field]');
        }
        if (!input) return;
        input.focus();
        if (input.select && input.tagName === 'INPUT') {
            input.select();
        }
    }

    function mountDeleteMenu(row) {
        const actionCell = row.querySelector('.row-actions');
        if (!actionCell) return;
        actionCell.innerHTML = '' +
            '<details class=\"row-menu\">' +
            '<summary>...</summary>' +
            '<div class=\"row-menu-panel\">' +
            '<button type=\"button\" data-delete-row>Hapus</button>' +
            '</div>' +
            '</details>';
    }

    async function removeRow(table, row, config) {
        const id = row.dataset.id;
        if (!id) return;
        await requestJson(config.deleteUrl.replace('__ID__', id), 'DELETE');
        row.remove();
        emitSheetChanged(table);
    }

    function bindDeleteHandler(table, row, config) {
        const deleteButton = row.querySelector('[data-delete-row]');
        if (!deleteButton || deleteButton.dataset.bound === '1') {
            return;
        }
        deleteButton.dataset.bound = '1';
        deleteButton.addEventListener('click', async function () {
            await removeRow(table, row, config);
        });
    }

    function bindSwipeDelete(table, row, config) {
        if (row.dataset.swipeBound === '1') return;
        row.dataset.swipeBound = '1';

        let startX = 0;
        let startY = 0;
        let currentX = 0;
        let tracking = false;
        let swiping = false;

        function resetSwipeState() {
            row.style.transform = '';
            row.classList.remove('row-swipe-armed');
            currentX = 0;
            swiping = false;
        }

        row.addEventListener('touchstart', function (event) {
            if (row.dataset.newRow === '1' || !row.dataset.id) return;
            if (event.touches.length !== 1) return;
            if (event.target.closest('.row-menu')) return;

            startX = event.touches[0].clientX;
            startY = event.touches[0].clientY;
            currentX = 0;
            tracking = true;
            swiping = false;
            row.classList.remove('row-swipe-armed');
        }, { passive: true });

        row.addEventListener('touchmove', function (event) {
            if (!tracking || event.touches.length !== 1) return;

            const dx = event.touches[0].clientX - startX;
            const dy = event.touches[0].clientY - startY;

            if (!swiping) {
                if (Math.abs(dy) > 16 && Math.abs(dy) > Math.abs(dx)) {
                    tracking = false;
                    resetSwipeState();
                    return;
                }
                if (dx > 14 && Math.abs(dx) > Math.abs(dy) * 1.2) {
                    swiping = true;
                } else {
                    return;
                }
            }

            event.preventDefault();
            currentX = Math.max(0, dx);
            const limited = Math.min(120, currentX);
            row.style.transform = 'translateX(' + limited + 'px)';
            row.classList.toggle('row-swipe-armed', limited >= 84);
        }, { passive: false });

        row.addEventListener('touchend', function () {
            if (!tracking) return;
            tracking = false;

            const shouldDelete = swiping && currentX >= 84;
            resetSwipeState();

            if (shouldDelete) {
                removeRow(table, row, config).catch(console.error);
            }
        });

        row.addEventListener('touchcancel', function () {
            tracking = false;
            resetSwipeState();
        });
    }

    function autoGrowTextarea(textarea) {
        if (!textarea || textarea.tagName !== 'TEXTAREA') return;
        textarea.style.height = 'auto';
        textarea.style.height = Math.max(textarea.scrollHeight, 31) + 'px';
    }

    function appendInlineNewRow(table, config) {
        var existingInline = table.querySelector('tbody tr[data-row][data-new-row="1"]');
        if (existingInline) return existingInline;
        var newRow = buildNewRow(table);
        if (!newRow) return null;
        table.querySelector('tbody').appendChild(newRow);
        registerRowHandlers(table, newRow, config);
        return newRow;
    }

    function finalizeCreatedRow(table, row, config, record, snapshotData) {
        row.dataset.newRow = '0';
        row.dataset.id = String(record.id);
        row.dataset.snapshot = encodeSnapshot(snapshotData || collectRow(row));
        row.dataset.creating = '0';
        row.classList.remove('inline-add-row');
        mountDeleteMenu(row);
        bindDeleteHandler(table, row, config);
    }

    async function bulkCreateRows(table, rows, config) {
        if (!rows.length || !config.bulkCreateUrl) return;
        var payloadRows = rows.map(function (row) {
            return collectRow(row);
        });
        var result = await requestJson(config.bulkCreateUrl, 'POST', { rows: payloadRows });
        var records = (result && Array.isArray(result.records)) ? result.records : [];
        for (var i = 0; i < rows.length; i++) {
            var row = rows[i];
            var record = records[i];
            if (!record || !record.id) continue;
            finalizeCreatedRow(table, row, config, record, payloadRows[i]);
        }
        appendInlineNewRow(table, config);
        emitSheetChanged(table);
    }

    function registerRowHandlers(table, row, config) {
        if (row.dataset.fieldsBound !== '1') {
            row.dataset.fieldsBound = '1';
            row.querySelectorAll('[data-field]').forEach(function (input) {
                if (input.tagName === 'TEXTAREA') {
                    autoGrowTextarea(input);
                    if (input.dataset.autogrowBound !== '1') {
                        input.dataset.autogrowBound = '1';
                        input.addEventListener('input', function () {
                            autoGrowTextarea(input);
                        });
                    }
                }
                if (input.tagName === 'SELECT') {
                    applySelectTone(input);
                }
                input.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        row.dataset.pendingEnterField = row.dataset.newRow === '1'
                            ? (table.dataset.enterNextField || input.dataset.field || '')
                            : (input.dataset.field || '');
                        input.blur();
                        setTimeout(function () {
                            focusNextRowCell(table, row, input);
                        }, 0);
                    }
                    if (event.key === 'Delete' && event.shiftKey && row.dataset.newRow !== '1' && row.dataset.id) {
                        event.preventDefault();
                        removeRow(table, row, config).catch(console.error);
                    }
                });

                input.addEventListener('blur', function () {
                    syncRow(table, row, config).catch(console.error);
                });

                input.addEventListener('change', function () {
                    if (input.tagName === 'SELECT') {
                        applySelectTone(input);
                    }
                    syncRow(table, row, config).catch(console.error);
                });

                input.addEventListener('paste', function (event) {
                    handleSpreadsheetPaste(event, table, row, input, config).catch(console.error);
                });
            });
        }

        if (row.dataset.selectBound !== '1') {
            row.dataset.selectBound = '1';
            row.addEventListener('click', function (event) {
                if (!row.dataset.id || row.dataset.newRow === '1') return;
                if (!shouldHandleRowSelectionClick(event)) return;

                if (event.shiftKey && lastSelectedRow) {
                    const addToSelection = event.metaKey || event.ctrlKey;
                    if (!addToSelection) {
                        clearSelectedRows(true);
                    }
                    selectRangeRows(lastSelectedRow, row);
                    return;
                }

                if (event.metaKey || event.ctrlKey) {
                    toggleRowSelection(row);
                    return;
                }

                clearSelectedRows();
                selectRow(row, true);
                lastSelectedRow = row;
                updateBulkDeleteUI();
            });
        }

        bindDeleteHandler(table, row, config);
        bindSwipeDelete(table, row, config);
    }

    function parseClipboardMatrix(raw) {
        if (!raw) return [];
        var normalized = String(raw).replace(/\r\n/g, '\n').replace(/\r/g, '\n');
        var rows = normalized.split('\n');
        if (rows.length > 0 && rows[rows.length - 1] === '') {
            rows.pop();
        }
        return rows.map(function (line) {
            return line.split('\t');
        });
    }

    function getSheetRows(table) {
        return Array.from(table.querySelectorAll('tbody tr[data-row]'));
    }

    function getRowInputs(row) {
        return Array.from(row.querySelectorAll('[data-field]'));
    }

    function ensureRowAt(table, rowIndex, config) {
        var rows = getSheetRows(table);
        while (rowIndex >= rows.length) {
            var newRow = buildNewRow(table);
            if (!newRow) break;
            table.querySelector('tbody').appendChild(newRow);
            registerRowHandlers(table, newRow, config);
            rows = getSheetRows(table);
        }
        return rows[rowIndex] || null;
    }

    function setCellValue(input, value) {
        if (!input || input.readOnly || input.disabled) return;
        var raw = value === null || value === undefined ? '' : String(value).trim();
        if (input.tagName === 'SELECT') {
            var options = Array.from(input.options || []);
            var match = options.find(function (option) {
                return option.value === raw;
            });
            if (!match) {
                var lowered = raw.toLowerCase();
                match = options.find(function (option) {
                    return option.value.toLowerCase() === lowered || option.textContent.trim().toLowerCase() === lowered;
                });
            }
            if (match) {
                input.value = match.value;
                applySelectTone(input);
            }
            return;
        }

        input.value = raw;
        if (input.tagName === 'TEXTAREA') {
            autoGrowTextarea(input);
        }
    }

    async function runWithConcurrency(items, limit, worker) {
        if (!items.length) return;
        const queue = items.slice();
        const workers = new Array(Math.min(limit, queue.length)).fill(null).map(async function () {
            while (queue.length > 0) {
                const item = queue.shift();
                if (item === undefined) return;
                await worker(item);
            }
        });
        await Promise.allSettled(workers);
    }

    async function handleSpreadsheetPaste(event, table, row, input, config) {
        var clipboard = event.clipboardData || window.clipboardData;
        if (!clipboard) return;
        var matrix = parseClipboardMatrix(clipboard.getData('text'));
        if (!matrix.length) return;

        var isMultiCell = matrix.length > 1 || (matrix[0] && matrix[0].length > 1);
        if (!isMultiCell) return;

        event.preventDefault();

        var rows = getSheetRows(table);
        var startRowIndex = rows.indexOf(row);
        if (startRowIndex < 0) return;

        var startInputs = getRowInputs(row);
        var startColIndex = startInputs.indexOf(input);
        if (startColIndex < 0) return;

        var touchedRows = [];

        for (var r = 0; r < matrix.length; r++) {
            var targetRow = ensureRowAt(table, startRowIndex + r, config);
            if (!targetRow) break;

            if (touchedRows.indexOf(targetRow) === -1) {
                touchedRows.push(targetRow);
            }

            var targetInputs = getRowInputs(targetRow);
            for (var c = 0; c < matrix[r].length; c++) {
                var targetColIndex = startColIndex + c;
                if (targetColIndex >= targetInputs.length) continue;
                setCellValue(targetInputs[targetColIndex], matrix[r][c]);
            }
        }

        var newRowsToCreate = [];
        var existingRowsToUpdate = [];
        touchedRows.forEach(function (targetRow) {
            var isNewRow = targetRow.dataset.newRow === '1';
            var data = collectRow(targetRow);
            if (isNewRow && hasRequiredValues(data, config.required)) {
                newRowsToCreate.push(targetRow);
                return;
            }
            existingRowsToUpdate.push(targetRow);
        });

        setSaving(true);
        try {
            if (newRowsToCreate.length > 0) {
                if (config.bulkCreateUrl) {
                    await bulkCreateRows(table, newRowsToCreate, config);
                } else {
                    await runWithConcurrency(newRowsToCreate, 6, async function (targetRow) {
                        await syncRow(table, targetRow, config);
                    });
                }
            }

            await runWithConcurrency(existingRowsToUpdate, 8, async function (targetRow) {
                await syncRow(table, targetRow, config);
            });
        } finally {
            setSaving(false);
        }
        emitSheetChanged(table);
    }

    async function syncRow(table, row, config) {
        const data = collectRow(row);
        const isNew = row.dataset.newRow === '1';

        if (isNew) {
            if (!hasRequiredValues(data, config.required)) {
                return;
            }

            if (row.dataset.creating === '1') {
                return;
            }

            row.dataset.creating = '1';
            setSaving(true);

            try {
                const result = await requestJson(config.createUrl, 'POST', data);
                row.dataset.newRow = '0';
                row.dataset.id = result.record.id;
                row.dataset.snapshot = encodeSnapshot(data);
                row.dataset.creating = '0';
                row.classList.remove('inline-add-row');
                mountDeleteMenu(row);
                bindDeleteHandler(table, row, config);
                emitSheetChanged(table);

                const newRow = buildNewRow(table);
                if (newRow) {
                    table.querySelector('tbody').appendChild(newRow);
                    registerRowHandlers(table, newRow, config);
                    const preferredField = row.dataset.pendingEnterField || table.dataset.enterNextField || 'name';
                    if (row.dataset.pendingEnterField) {
                        focusPreferredCell(newRow, preferredField);
                    }
                }
                row.dataset.pendingEnterField = '';
            } finally {
                setSaving(false);
            }

            return;
        }

        const nextSnapshot = encodeSnapshot(data);
        if (row.dataset.snapshot === nextSnapshot) {
            return;
        }

        if (!hasRequiredValues(data, config.required)) {
            return;
        }

        setSaving(true);
        try {
            await requestJson(config.updateUrl.replace('__ID__', row.dataset.id), 'PUT', data);
            row.dataset.snapshot = nextSnapshot;
            emitSheetChanged(table);
        } finally {
            setSaving(false);
        }
    }

    document.querySelectorAll('[data-sheet-table]').forEach(function (table) {
        const config = {
            createUrl: table.dataset.createUrl,
            bulkCreateUrl: table.dataset.bulkCreateUrl || '',
            updateUrl: table.dataset.updateUrl,
            deleteUrl: table.dataset.deleteUrl,
            bulkDeleteUrl: table.dataset.bulkDeleteUrl || '',
            required: (table.dataset.required || '').split(',').map(function (v) { return v.trim(); }).filter(Boolean)
        };
        tableConfigs.set(table, config);

        table.querySelectorAll('tbody tr[data-row]').forEach(function (row) {
            if (row.dataset.newRow !== '1') {
                row.dataset.snapshot = encodeSnapshot(collectRow(row));
            }
            registerRowHandlers(table, row, config);
        });
    });

    if (bulkDeleteButton) {
        bulkDeleteButton.addEventListener('click', async function () {
            if (selectedRows.size === 0) return;
            if (!confirm('Hapus semua row yang terpilih?')) return;

            const rows = Array.from(selectedRows).map(function (key) {
                return document.querySelector('tr[data-row][data-id][data-select-key="' + key + '"]');
            }).filter(Boolean);
            if (!rows.length) {
                clearSelectedRows();
                return;
            }

            setSaving(true);
            try {
                const tableMap = new Map();
                rows.forEach(function (row) {
                    var table = row.closest('[data-sheet-table]');
                    if (!table) return;
                    if (!tableMap.has(table)) tableMap.set(table, []);
                    tableMap.get(table).push(row);
                });

                const tableEntries = Array.from(tableMap.entries());
                await runWithConcurrency(tableEntries, 3, async function (entry) {
                    var table = entry[0];
                    var tableRows = entry[1];
                    var config = tableConfigs.get(table);
                    if (!config || !tableRows.length) return;

                    if (config.bulkDeleteUrl) {
                        var ids = tableRows
                            .map(function (row) { return Number(row.dataset.id || 0); })
                            .filter(function (id) { return Number.isInteger(id) && id > 0; });
                        if (ids.length) {
                            await requestJson(config.bulkDeleteUrl, 'POST', { ids: ids });
                        }
                        tableRows.forEach(function (row) { row.remove(); });
                        emitSheetChanged(table);
                        return;
                    }

                    await runWithConcurrency(tableRows, 4, async function (singleRow) {
                        await removeRow(table, singleRow, config);
                    });
                });
            } finally {
                setSaving(false);
                clearSelectedRows();
            }
        });
    }

    function applySelectTone(select) {
        const value = (select.value || '').toString().trim();
        const tones = [
            'sheet-tone-invited',
            'sheet-tone-attending',
            'sheet-tone-not_attending',
            'sheet-tone-pending',
            'sheet-tone-not_started',
            'sheet-tone-in_progress',
            'sheet-tone-done',
            'sheet-tone-ordered',
            'sheet-tone-on_delivery',
            'sheet-tone-arrived',
            'sheet-tone-complete',
            'sheet-tone-budget',
            'sheet-tone-expense'
        ];
        select.classList.remove.apply(select.classList, tones);
        if (value) {
            select.classList.add('sheet-tone-' + value);
        }
    }

    function formatCurrencyIdr(value) {
        var amount = Number(value);
        if (!Number.isFinite(amount) || amount <= 0) return '';
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(amount);
    }

    /* ─── SSE: Real-time remote changes ─────────────────────────── */

    var DB_TABLE_MAP = {
        'guests': 'guests',
        'engagement_tasks': 'tasks',
        'gifts': 'gifts',
        'expenses': 'expenses'
    };

    var URL_TABLE_MAP = {
        '/tasks': 'engagement_tasks',
        '/gifts': 'gifts',
        '/expenses': 'expenses',
        '/guests': 'guests'
    };

    function getTableConfig(sheetTable) {
        return {
            createUrl: sheetTable.dataset.createUrl,
            bulkCreateUrl: sheetTable.dataset.bulkCreateUrl || '',
            updateUrl: sheetTable.dataset.updateUrl,
            deleteUrl: sheetTable.dataset.deleteUrl,
            bulkDeleteUrl: sheetTable.dataset.bulkDeleteUrl || '',
            required: (sheetTable.dataset.required || '').split(',').map(function (v) { return v.trim(); }).filter(Boolean)
        };
    }

    function findSheetTablesForDbTable(dbTable) {
        var all = document.querySelectorAll('[data-sheet-table]');
        var results = [];
        for (var i = 0; i < all.length; i++) {
            var t = all[i];
            var createUrl = t.dataset.createUrl || '';
            for (var fragment in URL_TABLE_MAP) {
                if (createUrl.indexOf(fragment) !== -1 && URL_TABLE_MAP[fragment] === dbTable) {
                    results.push(t);
                    break;
                }
            }
        }
        return results;
    }

    function findGuestTable(eventType, side) {
        return document.querySelector(
            'table[data-sheet-name="guests"][data-event-type="' + eventType + '"][data-side="' + side + '"]'
        );
    }

    function applyDataToRow(row, data, dbTable) {
        Object.keys(data).forEach(function (column) {
            if (column === 'id' || column === 'created_at' || column === 'updated_at') return;
            var input = row.querySelector('[data-field="' + column + '"]');
            if (!input) return;

            var value = data[column];
            if (value === null || value === undefined) value = '';

            if (input.dataset.currencyIdr === '1' && input !== document.activeElement) {
                var num = Number(value);
                input.value = (Number.isFinite(num) && num > 0) ? formatCurrencyIdr(num) : '';
            } else if (input.tagName === 'SELECT') {
                input.value = String(value);
                applySelectTone(input);
            } else {
                input.value = String(value);
            }
        });
    }

    function flashRow(row) {
        row.style.transition = 'background-color 0.4s ease';
        var cells = row.querySelectorAll('td');
        cells.forEach(function (td) { td.style.backgroundColor = '#d4edda'; });
        setTimeout(function () {
            cells.forEach(function (td) { td.style.backgroundColor = ''; });
            setTimeout(function () {
                row.style.transition = '';
            }, 400);
        }, 1200);
    }

    function applyRemoteUpdate(dbTable, recordId, data) {
        var rows = document.querySelectorAll('tr[data-row][data-id="' + recordId + '"]');

        rows.forEach(function (row) {
            if (row.contains(document.activeElement)) {
                row.dataset.pendingRemoteUpdate = JSON.stringify(data);
                row.dataset.pendingRemoteTable = dbTable;
                return;
            }

            applyDataToRow(row, data, dbTable);
            row.dataset.snapshot = encodeSnapshot(collectRow(row));
            flashRow(row);

            var parentTable = row.closest('[data-sheet-table]');
            if (parentTable) emitSheetChanged(parentTable);
        });

        if (dbTable === 'guests' && data) {
            handleGuestTableMove(recordId, data);
        }
    }

    function applyRemoteInsert(dbTable, recordId, data) {
        var targetTables;

        if (dbTable === 'guests' && data) {
            var gt = findGuestTable(data.event_type, data.side);
            targetTables = gt ? [gt] : [];
        } else {
            targetTables = findSheetTablesForDbTable(dbTable);
        }

        targetTables.forEach(function (targetTable) {
            if (targetTable.querySelector('tr[data-row][data-id="' + recordId + '"]')) return;

            var newRow = buildNewRow(targetTable);
            if (!newRow) return;

            newRow.dataset.newRow = '0';
            newRow.dataset.id = recordId;
            newRow.classList.remove('inline-add-row');

            applyDataToRow(newRow, data, dbTable);
            mountDeleteMenu(newRow);

            var addRow = targetTable.querySelector('tbody tr[data-new-row="1"]');
            if (addRow) {
                targetTable.querySelector('tbody').insertBefore(newRow, addRow);
            } else {
                targetTable.querySelector('tbody').appendChild(newRow);
            }

            newRow.dataset.snapshot = encodeSnapshot(collectRow(newRow));

            var config = getTableConfig(targetTable);
            registerRowHandlers(targetTable, newRow, config);

            flashRow(newRow);
            emitSheetChanged(targetTable);
        });
    }

    function applyRemoteDelete(dbTable, recordId) {
        var rows = document.querySelectorAll('tr[data-row][data-id="' + recordId + '"]');
        rows.forEach(function (row) {
            var parentTable = row.closest('[data-sheet-table]');
            row.remove();
            if (parentTable) emitSheetChanged(parentTable);
        });
    }

    function handleGuestTableMove(recordId, data) {
        if (!data.event_type || !data.side) return;
        var correctTable = findGuestTable(data.event_type, data.side);
        if (!correctTable) return;

        var existingInCorrect = correctTable.querySelector('tr[data-row][data-id="' + recordId + '"]');
        if (existingInCorrect) return;

        var rowElsewhere = document.querySelector('tr[data-row][data-id="' + recordId + '"]');
        if (!rowElsewhere) return;

        var oldTable = rowElsewhere.closest('[data-sheet-table]');
        rowElsewhere.remove();
        if (oldTable) emitSheetChanged(oldTable);

        var tbody = correctTable.querySelector('tbody');
        var addRow = tbody.querySelector('tr[data-new-row="1"]');
        if (addRow) {
            tbody.insertBefore(rowElsewhere, addRow);
        } else {
            tbody.appendChild(rowElsewhere);
        }

        applyDataToRow(rowElsewhere, data, 'guests');
        rowElsewhere.dataset.snapshot = encodeSnapshot(collectRow(rowElsewhere));

        var config = getTableConfig(correctTable);
        registerRowHandlers(correctTable, rowElsewhere, config);

        flashRow(rowElsewhere);
        emitSheetChanged(correctTable);
    }

    var reorderBatchTimer = null;
    var reorderBatchQueue = [];

    function handleReorderBatch(data) {
        reorderBatchQueue.push(data);
        clearTimeout(reorderBatchTimer);
        reorderBatchTimer = setTimeout(function () {
            reorderBatchQueue.forEach(function (item) {
                var row = document.querySelector('tr[data-row][data-id="' + item.id + '"]');
                if (row) {
                    var sortInput = row.querySelector('[data-field="sort_order"]');
                    if (sortInput) sortInput.value = String(item.sort_order);
                    row.dataset.snapshot = encodeSnapshot(collectRow(row));
                }
            });

            var sample = reorderBatchQueue[0];
            if (sample && sample.event_type && sample.side) {
                resortGuestTable(sample.event_type, sample.side);
            }
            reorderBatchQueue = [];
        }, 500);
    }

    function resortGuestTable(eventType, side) {
        var table = findGuestTable(eventType, side);
        if (!table) return;

        var tbody = table.querySelector('tbody');
        var rows = Array.from(tbody.querySelectorAll('tr[data-row][data-id]'));
        var addRow = tbody.querySelector('tr[data-new-row="1"]');

        rows.sort(function (a, b) {
            var aInput = a.querySelector('[data-field="sort_order"]');
            var bInput = b.querySelector('[data-field="sort_order"]');
            var aOrder = aInput ? parseInt(aInput.value || '0', 10) : 0;
            var bOrder = bInput ? parseInt(bInput.value || '0', 10) : 0;
            return aOrder - bOrder;
        });

        rows.forEach(function (row) {
            tbody.insertBefore(row, addRow);
        });
    }

    function updateConnectionIndicator(connected) {
        var indicator = document.getElementById('sse-status');
        if (!indicator) {
            indicator = document.createElement('span');
            indicator.id = 'sse-status';
            indicator.style.cssText = 'display:inline-block;width:8px;height:8px;border-radius:50%;margin-left:8px;vertical-align:middle;';
            if (hint) hint.appendChild(indicator);
        }
        indicator.style.backgroundColor = connected ? '#28a745' : '#dc3545';
        indicator.title = connected ? 'Real-time sync active' : 'Reconnecting...';
    }

    document.addEventListener('focusout', function (event) {
        var row = event.target.closest('tr[data-row]');
        if (!row || !row.dataset.pendingRemoteUpdate) return;

        setTimeout(function () {
            if (!row.dataset.pendingRemoteUpdate) return;
            try {
                var pending = JSON.parse(row.dataset.pendingRemoteUpdate);
                var dbTable = row.dataset.pendingRemoteTable || '';
                delete row.dataset.pendingRemoteUpdate;
                delete row.dataset.pendingRemoteTable;
                applyDataToRow(row, pending, dbTable);
                row.dataset.snapshot = encodeSnapshot(collectRow(row));
                var parentTable = row.closest('[data-sheet-table]');
                if (parentTable) emitSheetChanged(parentTable);
            } catch (e) {
                console.error('Error applying pending remote update:', e);
            }
        }, 150);
    }, true);

    var sseDisabled = @json((bool) config('app.disable_sse'));

    (function initSSE() {
        if (sseDisabled) {
            updateConnectionIndicator(false);
            return;
        }

        var eventSource = null;
        var reconnectAttempts = 0;
        var disconnectedAt = 0;

        function connect() {
            if (eventSource) {
                eventSource.close();
            }

            eventSource = new EventSource('/events?client_id=' + encodeURIComponent(clientId));

            eventSource.addEventListener('table_change', function (event) {
                reconnectAttempts = 0;
                try {
                    var payload = JSON.parse(event.data);
                    var op = payload.operation;
                    var dbTable = payload.table;
                    var recordId = payload.record_id;
                    var data = payload.data;

                    if (dbTable === 'guests' && op === 'UPDATE' && data && data.sort_order !== undefined) {
                        var existingRow = document.querySelector('tr[data-row][data-id="' + recordId + '"]');
                        var sortInput = existingRow ? existingRow.querySelector('[data-field="sort_order"]') : null;
                        var oldSort = sortInput ? sortInput.value : null;
                        if (oldSort !== null && String(data.sort_order) !== oldSort) {
                            handleReorderBatch(data);
                        }
                    }

                    if (op === 'UPDATE') {
                        applyRemoteUpdate(dbTable, recordId, data);
                    } else if (op === 'INSERT') {
                        applyRemoteInsert(dbTable, recordId, data);
                    } else if (op === 'DELETE') {
                        applyRemoteDelete(dbTable, recordId);
                    }
                } catch (e) {
                    console.error('SSE parse error:', e);
                }
            });

            eventSource.addEventListener('open', function () {
                if (reconnectAttempts > 0 && disconnectedAt > 0) {
                    var elapsed = Date.now() - disconnectedAt;
                    if (elapsed > 60000) {
                        location.reload();
                        return;
                    }
                }
                reconnectAttempts = 0;
                disconnectedAt = 0;
                updateConnectionIndicator(true);
            });

            eventSource.addEventListener('error', function () {
                updateConnectionIndicator(false);
                eventSource.close();
                if (disconnectedAt === 0) disconnectedAt = Date.now();
                reconnectAttempts++;
                var delay = Math.min(1000 * Math.pow(2, reconnectAttempts - 1), 30000);
                setTimeout(connect, delay);
            });
        }

        window.addEventListener('beforeunload', function () {
            if (eventSource) eventSource.close();
        });

        connect();
    })();
})();
</script>
<script>
(function () {
    var nav = document.querySelector('.planner-main-nav');
    if (!nav) return;

    var hideTimer = null;
    var stickyThreshold = Math.max((nav.offsetTop || 0) - 10, 0);

    function isStickyActive() {
        return window.scrollY > stickyThreshold;
    }

    function clearHideTimer() {
        if (!hideTimer) return;
        clearTimeout(hideTimer);
        hideTimer = null;
    }

    function showNav() {
        nav.classList.remove('sticky-hidden');
    }

    function scheduleHide() {
        clearHideTimer();
        if (!isStickyActive()) {
            showNav();
            return;
        }

        hideTimer = setTimeout(function () {
            if (isStickyActive()) {
                nav.classList.add('sticky-hidden');
            }
        }, 1000);
    }

    function handleScroll() {
        showNav();
        scheduleHide();
    }

    window.addEventListener('scroll', handleScroll, { passive: true });
    window.addEventListener('resize', function () {
        stickyThreshold = Math.max((nav.offsetTop || 0) - 10, 0);
        handleScroll();
    });

    handleScroll();
})();
</script>
@stack('page-scripts')
</body>
</html>
