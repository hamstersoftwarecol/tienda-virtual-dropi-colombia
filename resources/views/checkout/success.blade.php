@extends('layouts.app')

@section('title', '¡Pedido Confirmado!')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header Card -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-md-5 text-center mb-4">
                <div class="bg-success-subtle text-success rounded-circle d-inline-flex p-4 mb-3 mx-auto" style="width: 80px; height: 80px; align-items: center; justify-content: center;">
                    <i class="bi bi-check-lg display-5 fw-bold"></i>
                </div>
                <h2 class="fw-bold text-dark mb-2">¡Gracias por tu compra, {{ $order->customer_name }}!</h2>
                <p class="text-muted mb-4">
                    Tu pedido ha sido recibido con éxito y ya está siendo preparado para su despacho a {{ $order->shipping_city }}.
                </p>
                <div class="bg-light p-3 rounded-3 d-inline-flex flex-wrap justify-content-center gap-4 mx-auto border small">
                    <div>
                        <span class="text-muted d-block">Número de Pedido:</span>
                        <strong class="text-primary font-monospace">{{ $order->order_number }}</strong>
                    </div>
                    <div>
                        <span class="text-muted d-block">Fecha:</span>
                        <strong>{{ $order->created_at->format('d/m/Y H:i') }}</strong>
                    </div>
                    <div>
                        <span class="text-muted d-block">Estado del Pedido:</span>
                        <span class="badge {{ $order->status_badge }}">{{ $order->status_label }}</span>
                    </div>
                    <div>
                        <span class="text-muted d-block">Total Pagado:</span>
                        <strong class="text-dark fs-6">{{ format_cop($order->total) }}</strong>
                    </div>
                </div>
            </div>

            <!-- Receipt & Order Details Card -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-receipt text-primary me-2"></i>Detalle de la Orden (COP)</h5>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Imprimir Comprobante
                    </button>
                </div>

                <!-- Info Grid -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <h6 class="fw-bold text-muted small text-uppercase letter-spacing-1 mb-2">Información de Envío</h6>
                        <div class="small text-secondary">
                            <div class="fw-bold text-dark">{{ $order->customer_name }}</div>
                            <div>{{ $order->shipping_address }}</div>
                            <div>{{ $order->shipping_city }} {{ $order->shipping_postal_code ? ', C.P. ' . $order->shipping_postal_code : '' }}</div>
                            <div><i class="bi bi-telephone me-1"></i> {{ $order->customer_phone }}</div>
                            <div><i class="bi bi-envelope me-1"></i> {{ $order->customer_email }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-muted small text-uppercase letter-spacing-1 mb-2">Método de Pago</h6>
                        <div class="small text-secondary">
                            <div class="fw-bold text-dark">{{ $order->payment_method_label }}</div>
                            <div class="mt-1">
                                Estado de Pago: 
                                <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ $order->payment_status === 'paid' ? 'Pagado' : 'Pendiente de cobro' }}
                                </span>
                            </div>
                            @if($order->order_notes)
                                <div class="mt-2 text-muted"><em>"{{ $order->order_notes }}"</em></div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="table-responsive mb-4">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light small text-muted">
                            <tr>
                                <th class="py-2">Producto</th>
                                <th class="py-2 text-center">Cant.</th>
                                <th class="py-2 text-end">Precio Unit.</th>
                                <th class="py-2 text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            @if($item->product_image)
                                                <img src="{{ $item->product_image }}" alt="{{ $item->product_name }}" class="rounded-3 object-fit-cover" style="width: 50px; height: 50px;">
                                            @endif
                                            <div>
                                                <h6 class="mb-0 small fw-bold">{{ $item->product_name }}</h6>
                                                @if($item->product_sku)
                                                    <small class="text-muted font-monospace">SKU: {{ $item->product_sku }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 text-center">{{ $item->quantity }}</td>
                                    <td class="py-3 text-end">{{ format_cop($item->price) }}</td>
                                    <td class="py-3 text-end fw-bold text-dark">{{ format_cop($item->total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-top">
                            <tr>
                                <td colspan="3" class="text-end text-muted small py-2">Subtotal:</td>
                                <td class="text-end fw-semibold small py-2">{{ format_cop($order->subtotal) }}</td>
                            </tr>
                            @if($order->discount > 0)
                                <tr>
                                    <td colspan="3" class="text-end text-success small py-1">Descuento:</td>
                                    <td class="text-end text-success fw-semibold small py-1">-{{ format_cop($order->discount) }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="3" class="text-end text-muted small py-1">Envío:</td>
                                <td class="text-end small py-1">{{ $order->shipping_cost == 0 ? 'GRATIS' : format_cop($order->shipping_cost) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end text-muted small py-1">Impuestos (19% IVA):</td>
                                <td class="text-end small py-1">{{ format_cop($order->tax) }}</td>
                            </tr>
                            <tr class="fs-5 fw-bold">
                                <td colspan="3" class="text-end py-3 text-dark">Total:</td>
                                <td class="text-end py-3 text-primary">{{ format_cop($order->total) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Navigation buttons -->
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 pt-3 border-top">
                    <a href="{{ route('home') }}" class="btn btn-outline-primary rounded-pill px-4">
                        <i class="bi bi-arrow-left me-1"></i> Seguir Comprando
                    </a>
                    @auth
                        <a href="{{ route('orders.index') }}" class="btn btn-primary rounded-pill px-4">
                            <i class="bi bi-box-seam me-1"></i> Ver Mis Pedidos
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
