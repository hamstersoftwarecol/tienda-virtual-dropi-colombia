@extends('layouts.app')

@section('title', 'Pedido #' . $order->order_number)

@section('content')
<div class="container py-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-muted text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('orders.index') }}" class="text-muted text-decoration-none">Mis Pedidos</a></li>
                    <li class="breadcrumb-item active text-primary" aria-current="page">{{ $order->order_number }}</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-0 text-dark">Detalle del Pedido <span class="text-primary font-monospace">#{{ $order->order_number }}</span></h2>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Imprimir
            </button>
            <a href="{{ route('orders.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Volver a Mis Pedidos
            </a>
        </div>
    </div>

    <!-- Tracking Timeline Card -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
        <h6 class="fw-bold text-muted small text-uppercase letter-spacing-1 mb-4">Estado del Envío en Colombia</h6>
        
        @php
            $statuses = ['pending', 'processing', 'shipped', 'delivered'];
            $currentIndex = array_search($order->status, $statuses);
            if ($order->status === 'cancelled') {
                $currentIndex = -1;
            }
        @endphp

        @if($order->status === 'cancelled')
            <div class="alert alert-danger mb-0 rounded-3 d-flex align-items-center gap-2">
                <i class="bi bi-x-circle-fill fs-5"></i>
                <div>Este pedido ha sido <strong>Cancelado</strong>. Si tienes dudas, contáctanos a soporte.</div>
            </div>
        @else
            <div class="row g-2 text-center position-relative">
                <div class="col-3">
                    <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center fw-bold {{ $currentIndex >= 0 ? 'bg-success text-white' : 'bg-light text-muted' }}" style="width: 40px; height: 40px;">
                        <i class="bi bi-cart-check"></i>
                    </div>
                    <div class="small fw-semibold {{ $currentIndex >= 0 ? 'text-dark' : 'text-muted' }}">Pendiente</div>
                </div>
                <div class="col-3">
                    <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center fw-bold {{ $currentIndex >= 1 ? 'bg-success text-white' : 'bg-light text-muted' }}" style="width: 40px; height: 40px;">
                        <i class="bi bi-gear"></i>
                    </div>
                    <div class="small fw-semibold {{ $currentIndex >= 1 ? 'text-dark' : 'text-muted' }}">En Preparación</div>
                </div>
                <div class="col-3">
                    <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center fw-bold {{ $currentIndex >= 2 ? 'bg-success text-white' : 'bg-light text-muted' }}" style="width: 40px; height: 40px;">
                        <i class="bi bi-truck"></i>
                    </div>
                    <div class="small fw-semibold {{ $currentIndex >= 2 ? 'text-dark' : 'text-muted' }}">Enviado</div>
                </div>
                <div class="col-3">
                    <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center fw-bold {{ $currentIndex >= 3 ? 'bg-success text-white' : 'bg-light text-muted' }}" style="width: 40px; height: 40px;">
                        <i class="bi bi-house-check"></i>
                    </div>
                    <div class="small fw-semibold {{ $currentIndex >= 3 ? 'text-dark' : 'text-muted' }}">Entregado</div>
                </div>
            </div>
        @endif
    </div>

    <!-- Main Detail Row -->
    <div class="row g-4">
        <!-- Products List -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark">Artículos en este Pedido</h6>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light small text-muted">
                            <tr>
                                <th class="ps-4 py-2">Producto</th>
                                <th class="py-2 text-center">Cantidad</th>
                                <th class="py-2 text-end">Precio</th>
                                <th class="pe-4 py-2 text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr class="border-bottom">
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            @if($item->product_image)
                                                <img src="{{ $item->product_image }}" alt="{{ $item->product_name }}" class="rounded-3 object-fit-cover" style="width: 54px; height: 54px;">
                                            @endif
                                            <div>
                                                <h6 class="mb-0 fw-bold small">{{ $item->product_name }}</h6>
                                                @if($item->product_sku)
                                                    <span class="text-muted font-monospace small">SKU: {{ $item->product_sku }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 text-center fw-semibold">{{ $item->quantity }}</td>
                                    <td class="py-3 text-end">{{ format_cop($item->price) }}</td>
                                    <td class="pe-4 py-3 text-end fw-bold text-dark">{{ format_cop($item->total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Summary & Shipping Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Resumen de Pago (COP)</h6>

                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Subtotal:</span>
                    <span>{{ format_cop($order->subtotal) }}</span>
                </div>

                @if($order->discount > 0)
                    <div class="d-flex justify-content-between mb-2 small text-success">
                        <span>Descuento ({{ $order->coupon_code }}):</span>
                        <span>-{{ format_cop($order->discount) }}</span>
                    </div>
                @endif

                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Envío:</span>
                    <span>{{ $order->shipping_cost == 0 ? 'GRATIS' : format_cop($order->shipping_cost) }}</span>
                </div>

                <div class="d-flex justify-content-between mb-3 small">
                    <span class="text-muted">IVA (19%):</span>
                    <span>{{ format_cop($order->tax) }}</span>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-bold text-dark">Total:</span>
                    <span class="fs-5 fw-bold text-primary">{{ format_cop($order->total) }}</span>
                </div>

                <div class="small bg-light p-3 rounded-3 border">
                    <div class="text-muted mb-1">Método: <strong>{{ $order->payment_method_label }}</strong></div>
                    <div class="text-muted">Estado de Pago: <strong class="{{ $order->payment_status === 'paid' ? 'text-success' : 'text-warning' }}">{{ ucfirst($order->payment_status) }}</strong></div>
                </div>
            </div>

            <!-- Shipping address card -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Dirección de Entrega</h6>
                <div class="small text-secondary">
                    <div class="fw-bold text-dark">{{ $order->customer_name }}</div>
                    <div>{{ $order->shipping_address }}</div>
                    <div>{{ $order->shipping_city }} {{ $order->shipping_postal_code ? ', C.P. ' . $order->shipping_postal_code : '' }}</div>
                    <div class="mt-2"><i class="bi bi-telephone me-1"></i> {{ $order->customer_phone }}</div>
                    <div><i class="bi bi-envelope me-1"></i> {{ $order->customer_email }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
