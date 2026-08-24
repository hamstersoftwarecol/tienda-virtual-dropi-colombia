@extends('layouts.admin')

@section('title', 'Gestión de Pedidos')
@section('page_header', 'Control de Pedidos y Envíos (Colombia)')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Pedidos Recibidos</h4>
        <p class="text-muted small mb-0">Revisa y actualiza el estado de las órdenes de compra en Pesos Colombianos (COP).</p>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm rounded-4 bg-white p-3 mb-4">
    <form action="{{ route('admin.orders.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control border-start-0" placeholder="Buscar por número de pedido o nombre del cliente...">
            </div>
        </div>
        <div class="col-md-4">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Todos los Estados</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendiente</option>
                <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>En Proceso</option>
                <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Enviado</option>
                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Entregado</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-primary rounded-pill w-100">Filtrar</button>
            @if(request()->hasAny(['q', 'status']))
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill" title="Limpiar"><i class="bi bi-x-lg"></i></a>
            @endif
        </div>
    </form>
</div>

<!-- Orders Table -->
<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="bg-light small text-muted text-uppercase">
                <tr>
                    <th class="ps-4 py-3">Número de Pedido</th>
                    <th class="py-3">Cliente</th>
                    <th class="py-3">Fecha</th>
                    <th class="py-3">Total (COP)</th>
                    <th class="py-3">Pago</th>
                    <th class="py-3">Estado</th>
                    <th class="pe-4 py-3 text-end">Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
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
                            <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success-subtle text-success border border-success' : 'bg-warning-subtle text-warning border border-warning' }} rounded-pill px-2 py-1 small">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </td>
                        <td class="py-3">
                            <span class="badge {{ $order->status_badge }} rounded-pill px-3 py-1">
                                {{ $order->status_label }}
                            </span>
                        </td>
                        <td class="pe-4 py-3 text-end">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                Gestionar &rarr;
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No se encontraron pedidos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-center">
    {{ $orders->links('pagination::bootstrap-5') }}
</div>
@endsection
