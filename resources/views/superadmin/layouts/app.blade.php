<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SuperAdmin') — TMS Conext</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sa-bg: #0d1b2a;
            --sa-sidebar: #112236;
            --sa-card: #162840;
            --sa-accent: #FF6B35;
            --sa-accent2: #FFB347;
            --sa-text: #e2e8f0;
            --sa-muted: #7f9ab3;
            --sa-border: rgba(255,107,53,0.15);
            --sa-green: #22c55e;
            --sa-red: #ef4444;
            --sa-yellow: #f59e0b;
            --sa-blue: #3b82f6;
            --sidebar-w: 220px;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:var(--sa-bg); color:var(--sa-text); display:flex; min-height:100vh; }

        /* Sidebar */
        .sa-sidebar {
            width: var(--sidebar-w); background:var(--sa-sidebar);
            position:fixed; left:0; top:0; height:100vh;
            display:flex; flex-direction:column; padding:0;
            border-right:1px solid var(--sa-border); z-index:100;
            overflow-y:auto;
        }
        .sa-logo {
            padding:20px 20px 15px;
            border-bottom:1px solid var(--sa-border);
        }
        .sa-logo .badge-sa {
            display:inline-block; background:var(--sa-accent);
            color:#fff; font-size:9px; font-weight:700; letter-spacing:1.5px;
            padding:2px 8px; border-radius:4px; text-transform:uppercase;
            margin-bottom:6px;
        }
        .sa-logo h2 { font-size:18px; font-weight:700; color:#fff; line-height:1.2; }
        .sa-logo p { font-size:11px; color:var(--sa-muted); }

        .sa-nav { flex:1; padding:16px 10px; }
        .sa-nav-label { font-size:10px; font-weight:600; color:var(--sa-muted); letter-spacing:1.5px; text-transform:uppercase; padding:10px 10px 6px; }
        .sa-nav a {
            display:flex; align-items:center; gap:10px;
            padding:10px 12px; border-radius:8px; color:var(--sa-text);
            text-decoration:none; font-size:13px; font-weight:500;
            margin-bottom:2px; transition:all .2s;
        }
        .sa-nav a:hover { background:rgba(255,107,53,.12); color:var(--sa-accent); }
        .sa-nav a.active { background:var(--sa-accent); color:#fff; }
        .sa-nav a i { width:18px; text-align:center; font-size:14px; }

        .sa-footer { padding:16px; border-top:1px solid var(--sa-border); }
        .sa-footer form button {
            width:100%; display:flex; align-items:center; gap:10px;
            padding:10px 12px; border-radius:8px; background:transparent;
            color:var(--sa-muted); border:none; cursor:pointer; font-size:13px;
            font-family:inherit; transition:all .2s;
        }
        .sa-footer form button:hover { background:rgba(239,68,68,.15); color:var(--sa-red); }

        /* Main */
        .sa-main { flex:1; margin-left:var(--sidebar-w); display:flex; flex-direction:column; min-height:100vh; }
        .sa-header {
            background:var(--sa-sidebar); padding:16px 28px;
            display:flex; align-items:center; justify-content:space-between;
            border-bottom:1px solid var(--sa-border); position:sticky; top:0; z-index:50;
        }
        .sa-header h1 { font-size:20px; font-weight:700; color:var(--sa-accent); }
        .sa-header .sa-user { display:flex; align-items:center; gap:10px; color:var(--sa-muted); font-size:13px; }
        .sa-header .sa-user i { color:var(--sa-accent); }
        .sa-content { flex:1; padding:28px; }

        /* Alerts */
        .alert { padding:12px 16px; border-radius:8px; margin-bottom:20px; font-size:13px; display:flex; align-items:center; gap:10px; }
        .alert-success { background:rgba(34,197,94,.12); border:1px solid rgba(34,197,94,.3); color:#86efac; }
        .alert-error { background:rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.3); color:#fca5a5; }

        /* Cards */
        .sa-card { background:var(--sa-card); border-radius:12px; border:1px solid var(--sa-border); padding:22px; }
        .sa-grid { display:grid; gap:20px; }
        .sa-grid-4 { grid-template-columns:repeat(4,1fr); }
        .sa-grid-3 { grid-template-columns:repeat(3,1fr); }
        .sa-grid-2 { grid-template-columns:repeat(2,1fr); }

        /* Stat cards */
        .stat-card { background:var(--sa-card); border-radius:12px; border:1px solid var(--sa-border); padding:20px; }
        .stat-card .label { font-size:11px; font-weight:600; color:var(--sa-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px; }
        .stat-card .value { font-size:30px; font-weight:700; color:#fff; line-height:1; }
        .stat-card .sub { font-size:12px; color:var(--sa-muted); margin-top:6px; }
        .stat-card .icon { font-size:22px; margin-bottom:12px; }

        /* Badge */
        .badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; }
        .badge-green { background:rgba(34,197,94,.15); color:#86efac; }
        .badge-yellow { background:rgba(245,158,11,.15); color:#fcd34d; }
        .badge-red { background:rgba(239,68,68,.15); color:#fca5a5; }
        .badge-blue { background:rgba(59,130,246,.15); color:#93c5fd; }
        .badge-gray { background:rgba(127,154,179,.15); color:var(--sa-muted); }

        /* Table */
        .sa-table { width:100%; border-collapse:collapse; }
        .sa-table th { text-align:left; font-size:11px; font-weight:600; color:var(--sa-muted); text-transform:uppercase; letter-spacing:1px; padding:10px 14px; border-bottom:1px solid var(--sa-border); }
        .sa-table td { padding:12px 14px; border-bottom:1px solid rgba(255,255,255,.04); font-size:13px; vertical-align:middle; }
        .sa-table tr:hover td { background:rgba(255,255,255,.02); }
        .sa-table tr:last-child td { border-bottom:none; }

        /* Buttons */
        .btn { display:inline-flex; align-items:center; gap:6px; padding:8px 16px; border-radius:8px; font-size:13px; font-weight:500; cursor:pointer; border:none; text-decoration:none; transition:all .2s; font-family:inherit; }
        .btn-primary { background:var(--sa-accent); color:#fff; }
        .btn-primary:hover { background:#e5602d; }
        .btn-success { background:var(--sa-green); color:#fff; }
        .btn-success:hover { background:#16a34a; }
        .btn-warning { background:var(--sa-yellow); color:#000; }
        .btn-warning:hover { background:#d97706; }
        .btn-danger { background:var(--sa-red); color:#fff; }
        .btn-danger:hover { background:#dc2626; }
        .btn-ghost { background:rgba(255,255,255,.06); color:var(--sa-text); }
        .btn-ghost:hover { background:rgba(255,255,255,.12); }
        .btn-sm { padding:5px 10px; font-size:12px; border-radius:6px; }

        /* Form */
        .form-group { margin-bottom:18px; }
        .form-label { display:block; font-size:12px; font-weight:600; color:var(--sa-muted); margin-bottom:6px; text-transform:uppercase; letter-spacing:.5px; }
        .form-control {
            width:100%; padding:10px 14px; border-radius:8px;
            background:rgba(255,255,255,.05); border:1px solid var(--sa-border);
            color:var(--sa-text); font-size:14px; font-family:inherit;
            transition:border .2s; outline:none;
        }
        .form-control:focus { border-color:var(--sa-accent); }
        .form-control::placeholder { color:var(--sa-muted); }
        textarea.form-control { resize:vertical; min-height:80px; }

        /* Page header */
        .page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; }
        .page-header h2 { font-size:22px; font-weight:700; color:#fff; }

        /* Pagination */
        .pagination { display:flex; gap:6px; justify-content:center; margin-top:24px; }
        .pagination a, .pagination span { padding:6px 12px; border-radius:6px; font-size:13px; background:var(--sa-card); border:1px solid var(--sa-border); color:var(--sa-text); text-decoration:none; }
        .pagination .active span { background:var(--sa-accent); border-color:var(--sa-accent); color:#fff; }
        .pagination a:hover { background:rgba(255,107,53,.15); }

        /* Responsive */
        @media(max-width:768px){
            :root{--sidebar-w:0px;}
            .sa-sidebar{display:none;}
            .sa-grid-4{grid-template-columns:repeat(2,1fr);}
            .sa-grid-3{grid-template-columns:1fr;}
            .sa-grid-2{grid-template-columns:1fr;}
        }
    </style>
    @stack('styles')
</head>
<body>
    <aside class="sa-sidebar">
        <div class="sa-logo">
            <div class="badge-sa">🔐 Superadmin</div>
            <h2>TMS Conext</h2>
            <p>Painel de Controle</p>
        </div>
        <nav class="sa-nav">
            <div class="sa-nav-label">Principal</div>
            <a href="{{ route('superadmin.dashboard') }}" class="{{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a>

            <div class="sa-nav-label">Clientes</div>
            <a href="{{ route('superadmin.tenants.index') }}" class="{{ request()->routeIs('superadmin.tenants.*') ? 'active' : '' }}">
                <i class="fas fa-building"></i> Tenants
            </a>
            <a href="{{ route('superadmin.subscriptions.index') }}" class="{{ request()->routeIs('superadmin.subscriptions.*') ? 'active' : '' }}">
                <i class="fas fa-credit-card"></i> Assinaturas
            </a>

            <div class="sa-nav-label">Produto</div>
            <a href="{{ route('superadmin.plans.index') }}" class="{{ request()->routeIs('superadmin.plans.*') ? 'active' : '' }}">
                <i class="fas fa-tags"></i> Planos
            </a>
        </nav>
        <div class="sa-footer">
            <form method="POST" action="{{ route('superadmin.logout') }}">
                @csrf
                <button type="submit"><i class="fas fa-sign-out-alt"></i> Sair</button>
            </form>
        </div>
    </aside>

    <div class="sa-main">
        <header class="sa-header">
            <h1>@yield('page-title', 'Painel')</h1>
            <div class="sa-user">
                <i class="fas fa-shield-alt"></i>
                <span>{{ Auth::guard('superadmin')->user()->name }}</span>
            </div>
        </header>
        <main class="sa-content">
            @if(session('success'))
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
            @endif
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
