@extends('layouts.app')

@section('title', 'Carrito de Compras')

@section('content')
<div class="container py-5">
    <div class="mb-4">
        <h2 class="fw-bold mb-1 text-dark"><i class="bi bi-cart3 text-primary me-2"></i>Tu Carrito de Compras</h2>
        <p class="text-muted small">Revisa los artículos seleccionados en Pesos Colombianos (COP) antes de proceder al pago.</p>
    </div>

    @if(empty($items))
        <div class="card border-0 shadow-sm rounded-4 text-center py-5 bg-white">
            <div class="py-5">
                <div class="bg-light rounded-circle d-inline-flex p-4 mb-3 text-muted">
                    <i class="bi bi-cart-x fs-1"></i>
                </div>
                <h4 class="fw-bold mb-2">Tu carrito está vacío</h4>
                <p class="text-muted small mb-4">Aún no has agregado ningún producto a tu carrito de compras.</p>
                <a href="{{ route('shop.index') }}" class="btn btn-primary rounded-pill px-4 py-2">
                    <i class="bi bi-arrow-left me-1"></i> Explorar Catálogo
                </a>
            </div>
        </div>
    @else
        <div class="row g-4">
            <!-- Items List -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th class="ps-4 py-3" style="min-width: 260px;">Producto</th>
                                    <th class="py-3">Precio</th>
                                    <th class="py-3 text-center">Cantidad</th>
                                    <th class="py-3 text-end">Total</th>
                                    <th class="pe-4 py-3 text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $id => $item)
                                    <tr class="border-bottom">
                                        <td class="ps-4 py-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="rounded-3 object-fit-cover shadow-sm" style="width: 70px; height: 70px;">
                                                <div>
                                                    <h6 class="mb-0 fw-bold">
                                                        <a href="{{ route('shop.show', $item['slug']) }}" class="text-dark text-decoration-none">
                                                            {{ $item['name'] }}
                                                        </a>
                                                    </h6>
                                                    <small class="text-muted">{{ $item['category'] }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 fw-semibold">{{ format_cop($item['price']) }}</td>
                                        <td class="py-3 text-center">
                                            <form action="{{ route('cart.update') }}" method="POST" class="d-inline-flex align-items-center justify-content-center">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $item['id'] }}">
                                                <div class="quantity-control">
                                                    <button type="submit" name="quantity" value="{{ $item['quantity'] - 1 }}">-</button>
                                                    <input type="number" value="{{ $item['quantity'] }}" readonly>
                                                    <button type="submit" name="quantity" value="{{ $item['quantity'] + 1 }}" {{ $item['quantity'] >= $item['max_stock'] ? 'disabled' : '' }}>+</button>
                                                </div>
                                            </form>
                                        </td>
                                        <td class="py-3 text-end fw-bold text-primary">
                                            {{ format_cop($item['total']) }}
                                        </td>
                                        <td class="pe-4 py-3 text-end">
                                            <form action="{{ route('cart.remove', $item['id']) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger border-0 rounded-circle" title="Eliminar">
                                                    <i class="bi bi-trash3 fs-5"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Cart Actions Bottom -->
                    <div class="card-footer bg-white border-top p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <a href="{{ route('shop.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                            <i class="bi bi-arrow-left me-1"></i> Continuar Comprando
                        </a>
                        <form action="{{ route('cart.clear') }}" method="POST" onsubmit="return confirm('¿Estás seguro de vaciar el carrito?');">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                                <i class="bi bi-trash me-1"></i> Vaciar Carrito
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Coupon Box -->
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-ticket-perforated text-primary me-2"></i>Cupón de Descuento</h6>
                    @if($coupon)
                        <div class="alert alert-success d-flex justify-content-between align-items-center mb-0 rounded-3">
                            <div>
                                <i class="bi bi-check-circle-fill me-2"></i>
                                Cupón <strong>{{ $coupon->code }}</strong> aplicado con éxito ({{ $coupon->type === 'percent' ? $coupon->value . '% OFF' : format_cop($coupon->value) . ' OFF' }}).
                            </div>
                            <form action="{{ route('cart.coupon.remove') }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">Remover</button>
                            </form>
                        </div>
                    @else
                        <form action="{{ route('cart.coupon.apply') }}" method="POST" class="row g-2">
                            @csrf
                            <div class="col-sm-8">
                                <input type="text" name="code" class="form-control rounded-pill text-uppercase" placeholder="Ingresa tu código promocional (Ej: DESCUENTO10)" required>
                            </div>
                            <div class="col-sm-4">
                                <button type="submit" class="btn btn-primary rounded-pill w-100 fw-semibold">
                                    Aplicar Cupón
                                </button>
                            </div>
                        </form>
                        <small class="text-muted d-block mt-2">
                            Prueba usando cupones como: <strong class="badge bg-light text-dark border">DESCUENTO10</strong> o <strong class="badge bg-light text-dark border">PROMO20</strong>
                        </small>
                    @endif
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 sticky-top" style="top: 90px;">
                    <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">Resumen del Pedido</h5>

                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Subtotal:</span>
                        <span class="fw-semibold">{{ format_cop($subtotal) }}</span>
                    </div>

                    @if($discount > 0)
                        <div class="d-flex justify-content-between mb-2 small text-success">
                            <span>Descuento (Cupón):</span>
                            <span class="fw-semibold">-{{ format_cop($discount) }}</span>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Costo de Envío:</span>
                        @if($shipping == 0)
                            <span class="text-success fw-bold">¡GRATIS!</span>
                        @else
                            <span class="fw-semibold">{{ format_cop($shipping) }}</span>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between mb-3 small">
                        <span class="text-muted">IVA estimado (19%):</span>
                        <span class="fw-semibold">{{ format_cop($tax) }}</span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fs-5 fw-bold text-dark">Total:</span>
                        <span class="fs-4 fw-extrabold text-primary">{{ format_cop($total) }}</span>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm">
                            <i class="bi bi-credit-card me-2"></i> Continuar al Pago
                        </a>
                    </div>

                    <div class="text-center mt-3 small text-muted">
                        <i class="bi bi-shield-check text-success me-1"></i> Garantía de pago seguro cifrado SSL
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
