<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS & Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <style>
        :root {
            --reddit-orange: #ff4500;
            --reddit-orange-hover: #e03e00;
            --dark-bg: #0d1117;
            --darker-bg: #161b22;
            --card-bg: #21262d;
            --border-color: #30363d;
            --text-primary: #f0f6fc;
            --text-secondary: #8b949e;
        }

        body {
            background-color: var(--dark-bg);
            color: var(--text-primary);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-weight: 500;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 280px;
            background: linear-gradient(180deg, var(--darker-bg) 0%, var(--card-bg) 100%);
            padding: 20px 0;
            z-index: 1000;
            border-right: 1px solid var(--border-color);
            box-shadow: 4px 0 12px rgba(0,0,0,0.3);
        }
        
        .main-content {
            margin-left: 280px;
            padding: 20px;
            min-height: 100vh;
            background-color: var(--dark-bg);
        }
        
        .navbar {
            position: fixed;
            top: 0;
            left: 280px;
            right: 0;
            z-index: 1030;
            background: var(--darker-bg) !important;
            border-bottom: 1px solid var(--border-color);
            backdrop-filter: blur(10px);
            height: 70px;
        }
        
        .sidebar-brand {
            padding: 20px 25px;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--reddit-orange);
            text-decoration: none;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 20px;
            display: block;
        }
        
        .sidebar .nav-link {
            color: var(--text-secondary);
            padding: 12px 25px;
            border-radius: 0;
            margin: 2px 15px;
            font-weight: 600;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .sidebar .nav-link:hover {
            background: rgba(255, 69, 0, 0.1);
            color: var(--reddit-orange);
            border-left-color: var(--reddit-orange);
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            background: rgba(255, 69, 0, 0.15);
            color: var(--reddit-orange);
            border-left-color: var(--reddit-orange);
            font-weight: 700;
        }
        
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 12px;
            font-size: 1.1rem;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.4);
        }

        .card-header {
            background: var(--darker-bg);
            border-bottom: 1px solid var(--border-color);
            font-weight: 700;
            padding: 20px 25px;
            border-radius: 12px 12px 0 0 !important;
        }

        .stat-card {
            background: linear-gradient(135deg, var(--card-bg) 0%, var(--darker-bg) 100%);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--reddit-orange);
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(255, 69, 0, 0.2);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--reddit-orange);
            line-height: 1;
        }

        .stat-label {
            color: var(--text-secondary);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9rem;
        }

        .stat-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 2.5rem;
            color: rgba(255, 69, 0, 0.3);
        }

        .btn-primary {
            background: var(--reddit-orange);
            border-color: var(--reddit-orange);
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--reddit-orange-hover);
            border-color: var(--reddit-orange-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 69, 0, 0.3);
        }

        .table-dark {
            --bs-table-bg: var(--card-bg);
            --bs-table-striped-bg: var(--darker-bg);
            border: 1px solid var(--border-color);
        }

        .table-dark th {
            background: var(--darker-bg) !important;
            border-color: var(--border-color);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
        }

        .dropdown-menu {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.4);
        }

        .dropdown-item {
            color: var(--text-primary);
            font-weight: 500;
        }

        .dropdown-item:hover {
            background: rgba(255, 69, 0, 0.1);
            color: var(--reddit-orange);
        }

        .alert {
            border-radius: 8px;
            font-weight: 600;
            border: none;
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
            border-left: 4px solid #dc3545;
        }

        .form-control, .form-select {
            background: var(--darker-bg);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: 8px;
            font-weight: 500;
        }

        .form-control:focus, .form-select:focus {
            background: var(--darker-bg);
            border-color: var(--reddit-orange);
            box-shadow: 0 0 0 0.2rem rgba(255, 69, 0, 0.25);
            color: var(--text-primary);
        }

        .page-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 30px;
            position: relative;
            padding-left: 20px;
        }

        .page-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--reddit-orange);
            border-radius: 2px;
        }

        .chart-container {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
        }

        .metric-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .navbar {
                left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <span class="navbar-brand fw-bold fs-4">Dashboard Overview</span>
            
            <div class="dropdown">
                <a class="nav-link dropdown-toggle text-white d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-2 fs-5"></i>
                    <span class="fw-semibold">{{ Auth::user()->name ?? Auth::user()->username }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('users.show', Auth::user()) }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="{{ route('users.edit', Auth::user()) }}"><i class="bi bi-gear me-2"></i>Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="{{ route('home') }}" class="sidebar-brand text-decoration-none">
            <i class="bi bi-shop me-2"></i>My Dashboard
        </a>
        
        <nav class="nav flex-column">
            <span class="text-uppercase text-muted px-4 py-2 fw-bold" style="font-size: 0.8rem; letter-spacing: 1px;">MENU</span>
            
            <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a class="nav-link {{ request()->routeIs('pos.*') ? 'active' : '' }}" href="{{ route('pos.index') }}">
                <i class="bi bi-cart3"></i> POS
            </a>
            <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                <i class="bi bi-box-seam"></i> Products
            </a>
            <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                <i class="bi bi-people"></i> Users
            </a>
            
            <!-- Reports Dropdown -->
            <div class="dropdown dropend">
                <a class="nav-link dropdown-toggle {{ request()->routeIs('reports.*') ? 'active' : '' }}" 
                   href="#" role="button" data-bs-toggle="dropdown">
                    <i class="bi bi-graph-up"></i> Reports
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('reports.sales') }}">Sales Report</a></li>
                    <li><a class="dropdown-item" href="{{ route('reports.item-sales') }}">Item Sales</a></li>
                    <li><a class="dropdown-item" href="{{ route('reports.inventory') }}">Inventory</a></li>
                </ul>
            </div>
            
            @if(Auth::user()->is_admin)
            <a class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}" href="{{ route('audit.index') }}">
                <i class="bi bi-journal-text"></i> Audit Trail
            </a>
            @endif
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content" style="padding-top: 90px;">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set CSRF token for AJAX requests
        if (typeof $ !== 'undefined') {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        }
    </script>
    @stack('scripts')
</body>
</html>