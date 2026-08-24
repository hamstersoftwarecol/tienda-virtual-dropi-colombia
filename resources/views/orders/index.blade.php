@extends('layouts.app')

@section('title', 'Mis Pedidos')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 text-dark"><i class="bi bi-box-seam text-primary me-2"></i>Mis Pedidos</h2>
            <p class="text-muted small mb-0">Consulta el historial de todas tus compras y el estado de entrega en Colombia.</p>
        </div>
        <a href="{{ route('shop.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
            <i class="bi bi-cart3 me-1"></i> Ir al Catálogo
        </a>
    </div>

    @if($orders->isEmpty())
        <div class="card border-0 shadow-sm rounded-4 text-center py-5 bg-white">
            <div class="py-4">
                <div class="bg-light rounded-circle d-inline-flex p-4 mb-3 text-muted">
                    <i class="bi bi-bag-x fs-1"></i>
                </div>
                <h5 class="fw-bold mb-2">Aún no has realizado ninguna compra</h5>
                <p class="text-muted small mb-4">Descubre nuestros productos y realiza tu primer pedido hoy mismo.</p>
                <a href="{{ route('shop.index') }}" class="btn btn-primary rounded-pill px-4">
                    Explorar Catálogo
                </a>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light small text-muted text-uppercase">
                        <tr>
                            <th class="ps-4 py-3">Número de Pedido</th>
                            <th class="py-3">Fecha</th>
                            <th class="py-3">Artículos</th>
                            <th class="py-3">Estado</th>
                            <th class="py-3 text-end">Total (COP)</th>
                            <th class="pe-4 py-3 text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr class="border-bottom">
                                <td class="ps-4 py-3">
                                    <strong class="font-monospace text-primary">#{{ $order->order_number }}</strong>
                                </td>
                                <td class="py-3 small text-muted">
                                    {{ $order->created_at->format('d M Y, H:i') }}
                                </td>
                                <td class="py-3 small">
                                    <span class="badge bg-light text-dark border">{{ $order->items_count }} {{ Str::plural('producto', $order->items_count) }}</span>
                                </td>
                                <td class="py-3">
                                    <span class="badge {{ $order->status_badge }} rounded-pill px-3 py-1">
                                        {{ $order->status_label }}
                                    </span>
                                </td>
                                <td class="py-3 text-end fw-bold text-dark">
                                    {{ format_cop($order->total) }}
                                </td>
                                <td class="pe-4 py-3 text-end">
                                    <a href="{{ route('orders.show', $order->order_number) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        Detalles &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-center">
            {{ $orders->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
