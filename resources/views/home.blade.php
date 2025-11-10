@extends('layouts.app')

@section('content')
<div class="row">
    <!-- Metrics Grid -->
    <div class="col-12 mb-4">
        <div class="metric-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="stat-number">{{ \App\Models\Product::active()->count() }}</div>
                <div class="stat-label">Active Products</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div class="stat-number">{{ \App\Models\Sale::whereDate('created_at', today())->count() }}</div>
                <div class="stat-label">Today's Sales</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="stat-number">{{ \App\Models\Product::nearExpiring()->count() }}</div>
                <div class="stat-label">Near Expiry</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div class="stat-number">{{ \App\Models\Product::expired()->count() }}</div>
                <div class="stat-label">Expired Products</div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <!-- Sales Chart -->
    <div class="col-md-8">
        <div class="chart-container">
            <h5 class="fw-bold mb-3" style="color: var(--reddit-orange);">
                <i class="bi bi-graph-up me-2"></i>Sales Overview
            </h5>
            <canvas id="salesChart" height="100"></canvas>
        </div>
    </div>
    
    <!-- Near to Expire Chart -->
    <div class="col-md-4">
        <div class="chart-container">
            <h5 class="fw-bold mb-3" style="color: var(--reddit-orange);">
                <i class="bi bi-clock-history me-2"></i>Near to Expire Products
            </h5>
            <canvas id="expiryChart" height="200"></canvas>
        </div>
    </div>
</div>

<!-- Additional Charts Row -->
<div class="row mb-4">
    <!-- Fast Moving Products -->
    <div class="col-md-6">
        <div class="chart-container">
            <h5 class="fw-bold mb-3" style="color: var(--reddit-orange);">
                <i class="bi bi-speedometer2 me-2"></i>Fast Moving Products
            </h5>
            <canvas id="fastMovingChart" height="150"></canvas>
        </div>
    </div>
    
    <!-- Stock Status -->
    <div class="col-md-6">
        <div class="chart-container">
            <h5 class="fw-bold mb-3" style="color: var(--reddit-orange);">
                <i class="bi bi-pie-chart me-2"></i>Near to Out of Stock
            </h5>
            <canvas id="stockChart" height="150"></canvas>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-clock-history me-2" style="color: var(--reddit-orange);"></i>Recent Sales
                </h5>
            </div>
            <div class="card-body">
                @php
                    $recentSales = \App\Models\Sale::with('user')->latest()->limit(5)->get();
                @endphp
                
                @forelse($recentSales as $sale)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-3">
                        <div>
                            <div class="fw-bold">Sale #{{ $sale->id }}</div>
                            <small class="text-muted">
                                <i class="bi bi-person me-1"></i>
                                {{ $sale->user->name ?? $sale->user->username }}
                            </small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold" style="color: var(--reddit-orange);">
                                ₱{{ number_format($sale->total_amount, 2) }}
                            </div>
                            <small class="text-muted">{{ $sale->created_at->format('M j, H:i') }}</small>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No recent sales
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-exclamation-triangle me-2" style="color: var(--reddit-orange);"></i>Low Stock Alert
                </h5>
            </div>
            <div class="card-body">
                @php
                    $lowStock = \App\Models\Product::active()->where('quantity', '<=', 10)->limit(5)->get();
                @endphp
                
                @forelse($lowStock as $product)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-3">
                        <div>
                            <div class="fw-bold">{{ $product->name }}</div>
                            <small class="text-muted">
                                <i class="bi bi-building me-1"></i>
                                {{ $product->manufacturer }}
                            </small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-warning text-dark fw-bold">
                                {{ $product->quantity }} {{ $product->unit }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-check-circle fs-1 d-block mb-2" style="color: var(--reddit-orange);"></i>
                        All products have sufficient stock
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Chart.js Configuration with Dark Theme
Chart.defaults.color = '#8b949e';
Chart.defaults.borderColor = '#30363d';

// Get real data from database via AJAX
fetch('/dashboard/chart-data')
    .then(response => response.json())
    .then(data => {
        // Sales Chart with real monthly data
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: data.salesChart.labels,
                datasets: [{
                    label: 'Sales',
                    data: data.salesChart.data,
                    borderColor: '#ff4500',
                    backgroundColor: 'rgba(255, 69, 0, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            color: '#f0f6fc',
                            font: {
                                weight: 'bold'
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#30363d'
                        },
                        ticks: {
                            color: '#8b949e',
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            color: '#30363d'
                        },
                        ticks: {
                            color: '#8b949e'
                        }
                    }
                }
            }
        });

        // Near to Expire Chart with real product data
        const expiryCtx = document.getElementById('expiryChart').getContext('2d');
        new Chart(expiryCtx, {
            type: 'bar',
            data: {
                labels: data.nearExpiry.labels,
                datasets: [{
                    label: 'Days Left',
                    data: data.nearExpiry.data,
                    backgroundColor: data.nearExpiry.data.map(days => {
                        if (days <= 1) return '#dc3545';
                        if (days <= 3) return '#fd7e14';
                        if (days <= 7) return '#ffc107';
                        return '#ff8c00';
                    }),
                    borderColor: '#ff4500',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#30363d'
                        },
                        ticks: {
                            color: '#8b949e',
                            callback: function(value) {
                                return value + ' days';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#8b949e'
                        }
                    }
                }
            }
        });

        // Fast Moving Products Chart with real data
        const fastMovingCtx = document.getElementById('fastMovingChart').getContext('2d');
        new Chart(fastMovingCtx, {
            type: 'bar',
            data: {
                labels: data.fastMoving.labels,
                datasets: [{
                    label: 'Units Sold',
                    data: data.fastMoving.data,
                    backgroundColor: '#28a745',
                    borderColor: '#20c997',
                    borderWidth: 2
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: '#30363d'
                        },
                        ticks: {
                            color: '#8b949e'
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#8b949e'
                        }
                    }
                }
            }
        });

        // Low Stock Pie Chart with real data
        const stockCtx = document.getElementById('stockChart').getContext('2d');
        new Chart(stockCtx, {
            type: 'doughnut',
            data: {
                labels: data.lowStock.labels,
                datasets: [{
                    data: data.lowStock.data,
                    backgroundColor: ['#dc3545', '#ffc107', '#6f42c1', '#fd7e14', '#20c997'],
                    borderColor: '#21262d',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#f0f6fc',
                            padding: 15,
                            font: {
                                weight: 'bold'
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + ' units';
                            }
                        }
                    }
                }
            }
        });
    })
    .catch(error => {
        console.error('Error loading chart data:', error);
        // Show fallback message or empty charts
        document.querySelectorAll('canvas').forEach(canvas => {
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = '#8b949e';
            ctx.font = '16px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('No data available', canvas.width/2, canvas.height/2);
        });
    });
</script>
@endpush
@endsection