<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Engagement Planner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Engagement Planner</h1>
        <small class="text-muted">Laravel 8 | PHP 7.4</small>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Validasi gagal:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm"><div class="card-body">
                <div class="text-muted small">Total Undangan</div>
                <div class="h4 mb-0">{{ $stats['totalGuests'] }}</div>
                <div class="small">Hadir: {{ $stats['attendingGuests'] }} | Tidak hadir: {{ $stats['notAttendingGuests'] }}</div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm"><div class="card-body">
                <div class="text-muted small">Progress Task</div>
                <div class="h4 mb-0">{{ $stats['doneTasks'] }} / {{ $stats['pendingTasks'] + $stats['doneTasks'] }}</div>
                <div class="small">Pending: {{ $stats['pendingTasks'] }}</div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm"><div class="card-body">
                <div class="text-muted small">Total Budget</div>
                <div class="h4 mb-0">Rp {{ number_format($stats['totalBudget'], 0, ',', '.') }}</div>
                <div class="small">Budget seserahan: Rp {{ number_format($stats['giftBudget'], 0, ',', '.') }}</div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm"><div class="card-body">
                <div class="text-muted small">Expense / Sisa</div>
                <div class="h4 mb-0">Rp {{ number_format($stats['totalExpense'], 0, ',', '.') }}</div>
                <div class="small">Sisa: Rp {{ number_format($stats['remainingBudget'], 0, ',', '.') }}</div>
            </div></div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">1. Tracker Undangan</div>
                <div class="card-body">
                    <form action="{{ route('guests.store') }}" method="POST" class="row g-2 mb-3">
                        @csrf
                        <div class="col-md-3"><input name="name" class="form-control" placeholder="Nama" required></div>
                        <div class="col-md-2">
                            <select name="attendance_status" class="form-select" required>
                                <option value="invited">Diundang</option>
                                <option value="attending">Hadir</option>
                                <option value="not_attending">Tidak Hadir</option>
                            </select>
                        </div>
                        <div class="col-md-2"><input name="phone" class="form-control" placeholder="No. HP"></div>
                        <div class="col-md-3"><input name="notes" class="form-control" placeholder="Catatan"></div>
                        <div class="col-md-2 d-grid"><button class="btn btn-primary">Tambah</button></div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                            <tr><th>Nama</th><th>Status</th><th>Kontak</th><th>Notes</th><th>Aksi</th></tr>
                            </thead>
                            <tbody>
                            @forelse($guests as $guest)
                                <tr>
                                    <td>{{ $guest->name }}</td>
                                    <td>
                                        <form action="{{ route('guests.update', $guest) }}" method="POST" class="d-flex gap-2">
                                            @csrf
                                            @method('PUT')
                                            <select name="attendance_status" class="form-select form-select-sm">
                                                <option value="invited" @selected($guest->attendance_status === 'invited')>Diundang</option>
                                                <option value="attending" @selected($guest->attendance_status === 'attending')>Hadir</option>
                                                <option value="not_attending" @selected($guest->attendance_status === 'not_attending')>Tidak Hadir</option>
                                            </select>
                                            <input name="notes" class="form-control form-control-sm" value="{{ $guest->notes }}" placeholder="Notes">
                                            <button class="btn btn-sm btn-outline-primary">Simpan</button>
                                        </form>
                                    </td>
                                    <td>{{ $guest->phone }}</td>
                                    <td>{{ $guest->notes }}</td>
                                    <td>
                                        <form action="{{ route('guests.destroy', $guest) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted">Belum ada data undangan.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">2. List Hal yang Perlu Dilakukan / Disiapkan</div>
                <div class="card-body">
                    <form action="{{ route('tasks.store') }}" method="POST" class="row g-2 mb-3">
                        @csrf
                        <div class="col-md-4"><input name="title" class="form-control" placeholder="Judul task" required></div>
                        <div class="col-md-2"><input type="date" name="due_date" class="form-control"></div>
                        <div class="col-md-2">
                            <select name="status" class="form-select" required>
                                <option value="pending">Pending</option>
                                <option value="done">Selesai</option>
                            </select>
                        </div>
                        <div class="col-md-3"><input name="notes" class="form-control" placeholder="Catatan"></div>
                        <div class="col-md-1 d-grid"><button class="btn btn-primary">Tambah</button></div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                            <tr><th>Task</th><th>Due</th><th>Status</th><th>Notes</th><th>Aksi</th></tr>
                            </thead>
                            <tbody>
                            @forelse($tasks as $task)
                                <tr>
                                    <td>{{ $task->title }}</td>
                                    <td>{{ optional($task->due_date)->format('d M Y') }}</td>
                                    <td>
                                        <form action="{{ route('tasks.update', $task) }}" method="POST" class="d-flex gap-2">
                                            @csrf
                                            @method('PUT')
                                            <select name="status" class="form-select form-select-sm">
                                                <option value="pending" @selected($task->status === 'pending')>Pending</option>
                                                <option value="done" @selected($task->status === 'done')>Selesai</option>
                                            </select>
                                            <input name="notes" class="form-control form-control-sm" value="{{ $task->notes }}" placeholder="Notes">
                                            <button class="btn btn-sm btn-outline-primary">Simpan</button>
                                        </form>
                                    </td>
                                    <td>{{ $task->notes }}</td>
                                    <td>
                                        <form action="{{ route('tasks.destroy', $task) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted">Belum ada task.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">3. List Seserahan</div>
                <div class="card-body">
                    <form action="{{ route('gifts.store') }}" method="POST" class="row g-2 mb-3">
                        @csrf
                        <div class="col-md-3"><input name="name" class="form-control" placeholder="Nama item" required></div>
                        <div class="col-md-3"><input type="url" name="link" class="form-control" placeholder="Link produk"></div>
                        <div class="col-md-2"><input type="number" step="0.01" min="0" name="budget" class="form-control" placeholder="Budget"></div>
                        <div class="col-md-2">
                            <select name="status" class="form-select" required>
                                <option value="pending">Pending</option>
                                <option value="ordered">Sudah Dibeli</option>
                                <option value="arrived">Sudah Sampai</option>
                            </select>
                        </div>
                        <div class="col-md-2"><input name="notes" class="form-control" placeholder="Catatan"></div>
                        <div class="col-md-12 d-grid"><button class="btn btn-primary">Tambah Item Seserahan</button></div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                            <tr><th>Nama</th><th>Link</th><th>Budget</th><th>Status</th><th>Notes</th><th>Aksi</th></tr>
                            </thead>
                            <tbody>
                            @forelse($gifts as $gift)
                                <tr>
                                    <td>{{ $gift->name }}</td>
                                    <td>
                                        @if($gift->link)
                                            <a href="{{ $gift->link }}" target="_blank" rel="noopener">Buka Link</a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>Rp {{ number_format((float) $gift->budget, 0, ',', '.') }}</td>
                                    <td>
                                        <form action="{{ route('gifts.update', $gift) }}" method="POST" class="d-flex gap-2">
                                            @csrf
                                            @method('PUT')
                                            <select name="status" class="form-select form-select-sm">
                                                <option value="pending" @selected($gift->status === 'pending')>Pending</option>
                                                <option value="ordered" @selected($gift->status === 'ordered')>Sudah Dibeli</option>
                                                <option value="arrived" @selected($gift->status === 'arrived')>Sudah Sampai</option>
                                            </select>
                                            <input name="notes" class="form-control form-control-sm" value="{{ $gift->notes }}" placeholder="Notes">
                                            <button class="btn btn-sm btn-outline-primary">Simpan</button>
                                        </form>
                                    </td>
                                    <td>{{ $gift->notes }}</td>
                                    <td>
                                        <form action="{{ route('gifts.destroy', $gift) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-muted">Belum ada data seserahan.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">4. Total Budget & Expense Tracker</div>
                <div class="card-body">
                    <form action="{{ route('expenses.store') }}" method="POST" class="row g-2 mb-3">
                        @csrf
                        <div class="col-md-3"><input name="name" class="form-control" placeholder="Nama transaksi" required></div>
                        <div class="col-md-2"><input name="category" class="form-control" placeholder="Kategori"></div>
                        <div class="col-md-2">
                            <select name="type" class="form-select" required>
                                <option value="budget">Budget Masuk</option>
                                <option value="expense">Expense Keluar</option>
                            </select>
                        </div>
                        <div class="col-md-2"><input type="number" step="0.01" min="0" name="amount" class="form-control" placeholder="Nominal" required></div>
                        <div class="col-md-2"><input name="notes" class="form-control" placeholder="Catatan"></div>
                        <div class="col-md-1 d-grid"><button class="btn btn-primary">Tambah</button></div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                            <tr><th>Nama</th><th>Kategori</th><th>Tipe</th><th>Jumlah</th><th>Notes</th><th>Aksi</th></tr>
                            </thead>
                            <tbody>
                            @forelse($expenses as $expense)
                                <tr>
                                    <td>{{ $expense->name }}</td>
                                    <td>{{ $expense->category ?: '-' }}</td>
                                    <td>
                                        <span class="badge {{ $expense->type === 'budget' ? 'bg-success' : 'bg-danger' }}">
                                            {{ $expense->type === 'budget' ? 'Budget' : 'Expense' }}
                                        </span>
                                    </td>
                                    <td>Rp {{ number_format((float) $expense->amount, 0, ',', '.') }}</td>
                                    <td>{{ $expense->notes }}</td>
                                    <td>
                                        <form action="{{ route('expenses.destroy', $expense) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-muted">Belum ada catatan budget/expense.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
