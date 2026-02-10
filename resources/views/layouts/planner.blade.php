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
            padding: 0.7rem 0.6rem;
        }

        .table-clean td {
            vertical-align: middle;
            border-color: var(--line);
            border-top-width: 1px;
            border-bottom-width: 1px;
            padding: 0.45rem 0.5rem;
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

    <div class="autosave-hint mb-3"><span class="saving-dot"></span>Autosave aktif: Enter untuk lanjut ke row berikutnya, pindah field untuk simpan, Shift+Delete untuk hapus row.</div>

    @yield('content')
</div>

<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const hint = document.querySelector('.autosave-hint');
    var clientId = Date.now().toString(36) + Math.random().toString(36).substr(2, 9);
    window.__clientId = clientId;

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

    function registerRowHandlers(table, row, config) {
        if (row.dataset.fieldsBound !== '1') {
            row.dataset.fieldsBound = '1';
            row.querySelectorAll('[data-field]').forEach(function (input) {
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
            });
        }

        bindDeleteHandler(table, row, config);
        bindSwipeDelete(table, row, config);
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
            updateUrl: table.dataset.updateUrl,
            deleteUrl: table.dataset.deleteUrl,
            required: (table.dataset.required || '').split(',').map(function (v) { return v.trim(); }).filter(Boolean)
        };

        table.querySelectorAll('tbody tr[data-row]').forEach(function (row) {
            if (row.dataset.newRow !== '1') {
                row.dataset.snapshot = encodeSnapshot(collectRow(row));
            }
            registerRowHandlers(table, row, config);
        });
    });

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
            updateUrl: sheetTable.dataset.updateUrl,
            deleteUrl: sheetTable.dataset.deleteUrl,
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
@stack('page-scripts')
</body>
</html>
