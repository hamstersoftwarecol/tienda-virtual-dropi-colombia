@extends('layouts.admin')

@section('title', 'Gestionar Pedido #' . $order->order_number)
@section('page_header', 'Detalle de Pedido #' . $order->order_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Pedido <span class="text-primary font-monospace">#{{ $order->order_number }}</span></h4>
        <p class="text-muted small mb-0">Fecha de compra: {{ $order->created_at->format('d/m/Y H:i:s') }}</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Imprimir
        </button>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Volver a la lista
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Main Order Content -->
    <div class="col-lg-8">
        <!-- Products Table -->
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h5 class="fw-bold mb-0 text-dark">Artículos Comprados ({{ $order->items->count() }})</h5>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light small text-muted text-uppercase">
                        <tr>
                            <th class="ps-4 py-2">Producto</th>
                            <th class="py-2 text-center">Cant.</th>
                            <th class="py-2 text-end">Precio Unit.</th>
                            <th class="pe-4 py-2 text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr class="border-bottom">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        @if($item->product_image)
                                            <img src="{{ $item->product_image }}" alt="{{ $item->product_name }}" class="rounded-3 object-fit-cover shadow-sm" style="width: 50px; height: 50px;">
                                        @endif
                                        <div>
                                            <h6 class="mb-0 fw-bold small text-dark">{{ $item->product_name }}</h6>
                                            @if($item->product_sku)
                                                <small class="text-muted font-monospace">SKU: {{ $item->product_sku }}</small>
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
                    <tfoot class="border-top">
                        <tr>
                            <td colspan="3" class="text-end text-muted small py-2">Subtotal:</td>
                            <td class="pe-4 text-end fw-semibold small py-2">{{ format_cop($order->subtotal) }}</td>
                        </tr>
                        @if($order->discount > 0)
                            <tr>
                                <td colspan="3" class="text-end text-success small py-1">Descuento ({{ $order->coupon_code }}):</td>
                                <td class="pe-4 text-end text-success fw-semibold small py-1">-{{ format_cop($order->discount) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td colspan="3" class="text-end text-muted small py-1">Costo de Envío:</td>
                            <td class="pe-4 text-end small py-1">{{ $order->shipping_cost == 0 ? 'GRATIS' : format_cop($order->shipping_cost) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end text-muted small py-1">Impuestos (19% IVA):</td>
                            <td class="pe-4 text-end small py-1">{{ format_cop($order->tax) }}</td>
                        </tr>
                        <tr class="fs-5 fw-bold bg-light">
                            <td colspan="3" class="text-end py-3 text-dark">Total del Pedido:</td>
                            <td class="pe-4 text-end py-3 text-primary">{{ format_cop($order->total) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Customer & Shipping Details -->
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
            <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">Información del Cliente y Envío</h5>
            <div class="row g-3 small text-secondary">
                <div class="col-md-6">
                    <div class="text-muted small fw-bold text-uppercase">Nombre del Cliente</div>
                    <div class="fw-bold text-dark fs-6">{{ $order->customer_name }}</div>
                    @if($order->user)
                        <span class="badge bg-primary-subtle text-primary">Usuario Registrado</span>
                    @else
                        <span class="badge bg-secondary-subtle text-muted">Invitado</span>
                    @endif
                </div>
                <div class="col-md-6">
                    <div class="text-muted small fw-bold text-uppercase">Contacto</div>
                    <div><i class="bi bi-envelope me-1"></i> {{ $order->customer_email }}</div>
                    <div><i class="bi bi-telephone me-1"></i> {{ $order->customer_phone }}</div>
                </div>
                <div class="col-12 mt-3 pt-3 border-top">
                    <div class="text-muted small fw-bold text-uppercase">Dirección de Destino</div>
                    <div class="fw-semibold text-dark">{{ $order->shipping_address }}</div>
                    <div>{{ $order->shipping_city }} {{ $order->shipping_postal_code ? ', C.P. ' . $order->shipping_postal_code : '' }}</div>
                    @if($order->order_notes)
                        <div class="mt-2 p-2 bg-light rounded text-muted">
                            <strong>Notas de Entrega:</strong> {{ $order->order_notes }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Update Status Sidebar -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
            <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">Actualizar Estado del Pedido</h5>

            <form action="{{ route('admin.orders.status', $order->id) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="mb-3">
                    <label for="status" class="form-label fw-semibold small">Estado de la Orden</label>
                    <select name="status" id="status" class="form-select">
                        <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>⏳ Pendiente</option>
                        <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>⚙️ En Preparación</option>
                        <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>🚚 Enviado (En Tránsito)</option>
                        <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>✅ Entregado</option>
                        <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>❌ Cancelado</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="payment_status" class="form-label fw-semibold small">Estado del Pago</label>
                    <select name="payment_status" id="payment_status" class="form-select">
                        <option value="pending" {{ $order->payment_status === 'pending' ? 'selected' : '' }}>Pendiente de Pago</option>
                        <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Pagado</option>
                        <option value="failed" {{ $order->payment_status === 'failed' ? 'selected' : '' }}>Fallido</option>
                        <option value="refunded" {{ $order->payment_status === 'refunded' ? 'selected' : '' }}>Reembolsado</option>
                    </select>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary rounded-pill py-2 fw-semibold">
                        <i class="bi bi-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>

        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Método de Pago</h6>
            <div class="small">
                <div>Tipo: <strong>{{ $order->payment_method_label }}</strong></div>
                <div class="mt-2">
                    Estado: <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }}">{{ ucfirst($order->payment_status) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
