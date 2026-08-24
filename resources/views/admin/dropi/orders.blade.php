@extends('layouts.admin')

@section('title', 'Despachos Dropi')
@section('page_header', 'Tablero de Despachos Dropi & Guías de Transporte')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-truck text-primary me-2"></i>Monitoreo de Despachos Dropi</h4>
        <p class="text-muted small mb-0">Rastrea guías de transportadoras (Coordinadora, Servientrega, Interrapidísimo, Envia) y el estado de entrega a tus clientes.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.orders.create') }}" class="btn btn-primary rounded-pill px-3 shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Generar Nuevo Pedido
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="card border-0 shadow-sm rounded-4 bg-white p-3 mb-4">
    <form action="{{ route('admin.dropi.orders') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-6">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control border-start-0" placeholder="Buscar por número de orden, cliente, cédula o número de guía...">
            </div>
        </div>

        <div class="col-md-4">
            <select name="dropi_status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Todos los Estados Dropi</option>
                <option value="unassigned" {{ request('dropi_status') === 'unassigned' ? 'selected' : '' }}>Sin Despachar a Dropi</option>
                <option value="in_preparation" {{ request('dropi_status') === 'in_preparation' ? 'selected' : '' }}>En Preparación Dropi</option>
                <option value="dispatched" {{ request('dropi_status') === 'dispatched' ? 'selected' : '' }}>En Tránsito (Transportadora)</option>
                <option value="delivered" {{ request('dropi_status') === 'delivered' ? 'selected' : '' }}>Entregado al Cliente</option>
            </select>
        </div>

        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-primary rounded-pill w-100">Filtrar</button>
            @if(request()->hasAny(['q', 'dropi_status']))
                <a href="{{ route('admin.dropi.orders') }}" class="btn btn-sm btn-outline-secondary rounded-pill" title="Limpiar"><i class="bi bi-x-lg"></i></a>
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
                    <th class="ps-4 py-3">Pedido</th>
                    <th class="py-3">Comprador / Cédula</th>
                    <th class="py-3">Destino (Colombia)</th>
                    <th class="py-3">Transportadora</th>
                    <th class="py-3">Guía / ID Dropi</th>
                    <th class="py-3">Estado Dropi</th>
                    <th class="pe-4 py-3 text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr class="border-bottom">
                        <td class="ps-4 py-3">
                            <strong class="font-monospace text-primary">#{{ $order->order_number }}</strong>
                            <small class="text-muted d-block">{{ $order->created_at->format('d/m/Y H:i') }}</small>
                        </td>
                        <td class="py-3">
                            <div class="fw-semibold text-dark">{{ $order->customer_name }}</div>
                            <small class="text-muted">C.C. {{ $order->recipient_dni ?: 'No registrada' }}</small>
                            <small class="text-muted d-block"><i class="bi bi-telephone"></i> {{ $order->customer_phone }}</small>
                        </td>
                        <td class="py-3 small">
                            <div class="fw-semibold">{{ $order->shipping_city }}</div>
                            <div class="text-muted">{{ $order->shipping_department ?: 'Colombia' }}</div>
                        </td>
                        <td class="py-3">
                            <span class="badge bg-light text-dark border px-2 py-1">
                                <i class="bi bi-box-seam me-1"></i> {{ $order->shipping_carrier ?: 'Coordinadora' }}
                            </span>
                        </td>
                        <td class="py-3">
                            @if($order->dropi_guide_number)
                                <strong class="font-monospace text-dark d-block" style="font-size: 0.85rem;">{{ $order->dropi_guide_number }}</strong>
                                <small class="text-muted font-monospace">{{ $order->dropi_order_id }}</small>
                            @else
                                <span class="badge bg-secondary-subtle text-muted">Sin Guía</span>
                            @endif
                        </td>
                        <td class="py-3">
                            <span class="badge {{ $order->dropi_status_badge }} rounded-pill px-3 py-1">
                                {{ $order->dropi_status_label }}
                            </span>
                        </td>
                        <td class="pe-4 py-3 text-end">
                            <div class="d-inline-flex gap-1">
                                @if(!$order->dropi_guide_number || $order->dropi_status === 'unassigned')
                                    <form action="{{ route('admin.dropi.orders.dispatch', $order->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3" title="Despachar a Dropi y Generar Guía">
                                            <i class="bi bi-send-fill me-1"></i> Despachar a Dropi
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.dropi.orders.sync', $order->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-info rounded-pill px-2" title="Avanzar rastreo / Sincronizar">
                                            <i class="bi bi-arrow-repeat"></i> Actualizar Guía
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-light border rounded-pill px-2" title="Ver Detalle">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No se encontraron despachos.</td>
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
