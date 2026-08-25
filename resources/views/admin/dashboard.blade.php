@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_header', 'Panel de Control y Métricas (COP)')

@section('content')
<!-- Dropi Fast Sync Hero Banner -->
<div class="card border-0 shadow-sm rounded-4 text-white mb-4 overflow-hidden position-relative" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4338ca 100%);">
    <div class="p-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-primary text-white fw-bold rounded-pill px-3 py-1">
                    <i class="bi bi-cloud-arrow-down-fill me-1"></i> Sincronización Dropi API
                </span>
                <span class="badge bg-white bg-opacity-25 text-white rounded-pill px-2 py-1 small">
                    <i class="bi bi-shield-check me-1"></i> Dropshipping Colombia
                </span>
            </div>
            <h4 class="fw-bold mb-1">Importar Productos desde Dropi</h4>
            <p class="mb-0 text-white-50 small">
                Explora el catálogo mayorista de Dropi e importa productos en un clic con margen de ganancia calculado en COP.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <a href="{{ route('admin.dropi.products.index') }}" class="btn btn-warning btn-lg rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2 text-dark">
                <i class="bi bi-box-arrow-in-down-fill"></i> Explorar Catálogo Dropi
            </a>
        </div>
    </div>
</div>

<!-- KPI Stat Cards -->
<div class="row g-3 mb-4">
    <!-- Revenue -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card shadow-sm d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-bold text-uppercase">Ventas Totales</span>
                <h3 class="fw-bold text-dark mt-1 mb-0">{{ format_cop($totalSales) }}</h3>
                <small class="text-success"><i class="bi bi-graph-up-arrow me-1"></i>Ingresos acumulados (COP)</small>
            </div>
            <div class="stat-icon bg-primary-subtle text-primary">
                <i class="bi bi-cash-stack"></i>
            </div>
        </div>
    </div>

    <!-- Orders -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card shadow-sm d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-bold text-uppercase">Pedidos Totales</span>
                <h3 class="fw-bold text-dark mt-1 mb-0">{{ $totalOrders }}</h3>
                <small class="text-info"><i class="bi bi-receipt me-1"></i>{{ $pendingOrders }} pendientes</small>
            </div>
            <div class="stat-icon bg-info-subtle text-info">
                <i class="bi bi-bag-check"></i>
            </div>
        </div>
    </div>

    <!-- Products -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card shadow-sm d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-bold text-uppercase">Productos Activos</span>
                <h3 class="fw-bold text-dark mt-1 mb-0">{{ $totalProducts }}</h3>
                @if($lowStockProducts > 0)
                    <small class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>{{ $lowStockProducts }} con bajo stock</small>
                @else
                    <small class="text-success"><i class="bi bi-check-circle me-1"></i>Inventario óptimo</small>
                @endif
            </div>
            <div class="stat-icon bg-warning-subtle text-warning">
                <i class="bi bi-box-seam"></i>
            </div>
        </div>
    </div>

    <!-- Customers -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card shadow-sm d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-bold text-uppercase">Clientes Registrados</span>
                <h3 class="fw-bold text-dark mt-1 mb-0">{{ $totalCustomers }}</h3>
                <small class="text-muted"><i class="bi bi-people me-1"></i>Compradores activos</small>
            </div>
            <div class="stat-icon bg-success-subtle text-success">
                <i class="bi bi-people-fill"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts & Highlights Row -->
<div class="row g-4 mb-4">
    <!-- Sales Overview -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0 text-dark">Resumen de Ventas (COP)</h5>
                <span class="badge bg-primary-subtle text-primary">Últimos meses</span>
            </div>
            <div style="height: 260px;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Selling Products -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-dark">Más Vendidos</h5>
                <a href="{{ route('admin.products.index') }}" class="small text-primary text-decoration-none">Ver todos</a>
            </div>
            <div class="d-flex flex-column gap-3">
                @foreach($topSellingProducts as $product)
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ $product->image }}" alt="{{ $product->name }}" class="rounded-3 object-fit-cover" style="width: 48px; height: 48px;">
                        <div class="flex-grow-1 overflow-hidden">
                            <h6 class="mb-0 small fw-bold text-truncate">{{ $product->name }}</h6>
                            <small class="text-muted">{{ format_cop($product->price) }}</small>
                        </div>
                        <span class="badge bg-light text-dark border">{{ $product->sales_count }} ventas</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders Table -->
<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark">Pedidos Recientes</h5>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
            Ver Todos los Pedidos
        </a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="bg-light small text-muted text-uppercase">
                <tr>
                    <th class="ps-4 py-3">Pedido</th>
                    <th class="py-3">Cliente</th>
                    <th class="py-3">Fecha</th>
                    <th class="py-3">Total (COP)</th>
                    <th class="py-3">Estado</th>
                    <th class="pe-4 py-3 text-end">Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOrders as $order)
                    <tr class="border-bottom">
                        <td class="ps-4 py-3">
                            <strong class="font-monospace text-primary">#{{ $order->order_number }}</strong>
                        </td>
                        <td class="py-3">
                            <div class="fw-semibold text-dark">{{ $order->customer_name }}</div>
                            <small class="text-muted">{{ $order->customer_email }}</small>
                        </td>
                        <td class="py-3 small text-muted">
                            {{ $order->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="py-3 fw-bold text-dark">
                            {{ format_cop($order->total) }}
                        </td>
                        <td class="py-3">
                            <span class="badge {{ $order->status_badge }} rounded-pill px-3 py-1">
                                {{ $order->status_label }}
                            </span>
                        </td>
                        <td class="pe-4 py-3 text-end">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-light border rounded-pill px-3">
                                Gestionar
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No hay pedidos registrados aún.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const monthlyData = @json($monthlySales);
    const labels = monthlyData.map(d => d.month);
    const revenues = monthlyData.map(d => parseFloat(d.revenue));

    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels.length > 0 ? labels : ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
            datasets: [{
                label: 'Ventas (COP)',
                data: revenues.length > 0 ? revenues : [2500000, 3800000, 5200000, 7100000, 6400000, 9500000],
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#4f46e5',
                pointRadius: 5,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        callback: function(val) { return '$ ' + val.toLocaleString('es-CO'); }
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
</script>
@endpush
