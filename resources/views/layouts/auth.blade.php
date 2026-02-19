<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Wedding Planner SaaS')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --bg: #f8f4ee;
            --surface: #fffefb;
            --line: #e4dbd0;
            --text: #2c2420;
            --muted: #7f756b;
            --accent: #b8956a;
        }
        body {
            margin: 0;
            min-height: 100vh;
            background: radial-gradient(1200px 600px at 10% -20%, rgba(184,149,106,0.16), transparent 60%), var(--bg);
            color: var(--text);
            font-family: "Plus Jakarta Sans", "Segoe UI", sans-serif;
        }
        .auth-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }
        .auth-card {
            width: 100%;
            max-width: 460px;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: var(--surface);
            box-shadow: 0 10px 28px rgba(44, 36, 32, 0.08);
        }
        .auth-card .card-body {
            padding: 1.25rem;
        }
        .auth-title {
            font-weight: 700;
            margin-bottom: 2px;
        }
        .auth-subtitle {
            color: var(--muted);
            font-size: 0.92rem;
            margin-bottom: 1rem;
        }
        .btn-accent {
            border-color: var(--accent);
            background: var(--accent);
            color: #fff;
            font-weight: 600;
        }
        .btn-accent:hover {
            border-color: #a78358;
            background: #a78358;
            color: #fff;
        }
        .form-label {
            font-weight: 600;
            font-size: 0.88rem;
            color: #5d5248;
        }
    </style>
</head>
<body>
<div class="auth-shell">
    <div class="auth-card">
        <div class="card-body">
            <h1 class="auth-title">@yield('heading', 'Wedding Planner')</h1>
            <div class="auth-subtitle">@yield('subtitle', 'Kelola workspace pernikahan bersama pasangan')</div>

            @if (session('status'))
                <div class="alert alert-success py-2">{{ session('status') }}</div>
            @endif

            @if (session('success'))
                <div class="alert alert-success py-2">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger py-2 mb-3">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</div>
</body>
</html>
