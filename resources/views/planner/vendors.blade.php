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
            <table class="table table-clean table-sm align-middle mb-0" data-sheet-table data-enter-next-field="vendor_name" data-create-url="{{ route('vendors.store') }}" data-bulk-create-url="{{ route('vendors.bulk-store') }}" data-bulk-delete-url="{{ route('vendors.bulk-destroy') }}" data-update-url="/vendors/__ID__" data-delete-url="/vendors/__ID__" data-required="vendor_name,status">
                <thead>
                <tr>
                    <th>Vendor Name</th>
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
                @foreach($vendors as $vendor)
                    <tr data-row data-id="{{ $vendor->id }}">
                        <td><input class="form-control form-control-sm sheet-cell" data-field="vendor_name" value="{{ $vendor->vendor_name }}"></td>
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
                    <td><input class="form-control form-control-sm sheet-cell" data-field="vendor_name" placeholder="Vendor Name"></td>
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
                        <td><input class="form-control form-control-sm sheet-cell" data-field="vendor_name" placeholder="Vendor Name"></td>
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

        document.addEventListener('sheet:changed', function (event) {
            var table = event.detail && event.detail.table;
            if (table && table.dataset.createUrl === '{{ route('vendors.store') }}') {
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

        recalcVendorStats();
    })();
</script>
@endpush
