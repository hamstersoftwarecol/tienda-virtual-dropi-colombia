@extends('layouts.app')

@section('title', 'Finalizar Compra - Checkout')

@section('content')
<div class="container py-4 py-lg-5">
    <div class="mb-4">
        <h2 class="fw-bold mb-1 text-dark"><i class="bi bi-shield-check text-primary me-2"></i>Finalizar Compra</h2>
        <p class="text-muted small">Completa tus datos de entrega en Colombia y selecciona tu método de pago preferido (Precios en COP).</p>
    </div>

    <form action="{{ route('checkout.process') }}" method="POST" id="checkoutForm">
        @csrf
        <div class="row g-4">
            <!-- Left: Shipping & Payment Information -->
            <div class="col-lg-8">
                <!-- Section 1: Customer & Shipping Info -->
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                    <h5 class="fw-bold mb-4 text-dark border-bottom pb-2">
                        <span class="badge bg-primary rounded-circle me-2">1</span> Datos de Contacto y Envío en Colombia
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="customer_name" class="form-label fw-semibold small">Nombre y Apellido *</label>
                            <input type="text" name="customer_name" id="customer_name" class="form-control rounded-3 @error('customer_name') is-invalid @enderror" value="{{ old('customer_name', $user?->name) }}" required placeholder="Ej: Carlos Mendoza">
                            @error('customer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="recipient_dni" class="form-label fw-semibold small">Cédula de Ciudadanía (C.C.) *</label>
                            <input type="text" name="recipient_dni" id="recipient_dni" class="form-control rounded-3 @error('recipient_dni') is-invalid @enderror" value="{{ old('recipient_dni', $user?->dni) }}" required placeholder="Ej: 1020456789">
                            @error('recipient_dni')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="customer_email" class="form-label fw-semibold small">Correo Electrónico *</label>
                            <input type="email" name="customer_email" id="customer_email" class="form-control rounded-3 @error('customer_email') is-invalid @enderror" value="{{ old('customer_email', $user?->email) }}" required placeholder="carlos@correo.com">
                            @error('customer_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="customer_phone" class="form-label fw-semibold small">Celular / WhatsApp *</label>
                            <input type="tel" name="customer_phone" id="customer_phone" class="form-control rounded-3 @error('customer_phone') is-invalid @enderror" value="{{ old('customer_phone', $user?->phone) }}" required placeholder="+57 300 123 4567">
                            @error('customer_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="shipping_department" class="form-label fw-semibold small">Departamento *</label>
                            <input type="text" name="shipping_department" id="shipping_department" class="form-control rounded-3 @error('shipping_department') is-invalid @enderror" value="{{ old('shipping_department', $user?->department ?: 'Cundinamarca') }}" required placeholder="Ej: Antioquia, Cundinamarca, Valle, Santander...">
                            @error('shipping_department')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="shipping_city" class="form-label fw-semibold small">Ciudad / Municipio *</label>
                            <input type="text" name="shipping_city" id="shipping_city" class="form-control rounded-3 @error('shipping_city') is-invalid @enderror" value="{{ old('shipping_city', $user?->city) }}" required placeholder="Ej: Bogotá D.C., Medellín, Cali, Barranquilla...">
                            @error('shipping_city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="shipping_address" class="form-label fw-semibold small">Dirección Exacta de Entrega *</label>
                            <input type="text" name="shipping_address" id="shipping_address" class="form-control rounded-3 @error('shipping_address') is-invalid @enderror" value="{{ old('shipping_address', $user?->address) }}" required placeholder="Ej: Carrera 15 # 85-30, Apto 402, Torre 3">
                            @error('shipping_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="order_notes" class="form-label fw-semibold small">Indicaciones para la Entrega (Opcional)</label>
                            <textarea name="order_notes" id="order_notes" rows="2" class="form-control rounded-3" placeholder="Ej: Portería 2, conjunto residencial, dejar con celaduría...">{{ old('order_notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Payment Method Selection -->
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                    <h5 class="fw-bold mb-4 text-dark border-bottom pb-2">
                        <span class="badge bg-primary rounded-circle me-2">2</span> Método de Pago
                    </h5>

                    <div class="d-flex flex-column gap-3 mb-2">
                        <!-- Option 1: Cash on Delivery -->
                        <div class="form-check p-3 border rounded-4 payment-option {{ old('payment_method', 'cash_on_delivery') === 'cash_on_delivery' ? 'border-primary bg-primary-subtle' : '' }}" style="transition: all 0.2s ease;">
                            <input class="form-check-input ms-0 me-3" type="radio" name="payment_method" id="pay_cod" value="cash_on_delivery" {{ old('payment_method', 'cash_on_delivery') === 'cash_on_delivery' ? 'checked' : '' }} onchange="updatePaymentSelection()">
                            <label class="form-check-label w-100 d-flex justify-content-between align-items-center cursor-pointer" for="pay_cod">
                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <strong class="text-dark fs-6">Pago Contra Entrega (Efectivo)</strong>
                                        <span class="badge bg-success rounded-pill px-2.5 py-1 small fw-bold">Recomendado</span>
                                    </div>
                                    <small class="text-muted d-block mt-1">Pagas en efectivo al mensajero cuando recibas tu paquete en la puerta de tu casa.</small>
                                </div>
                                <div class="fs-3 text-success ps-3">
                                    <i class="bi bi-cash-stack"></i>
                                </div>
                            </label>
                        </div>

                        <!-- Option 2: Bre-B (@ALM143) -->
                        <div class="form-check p-3 border rounded-4 payment-option {{ old('payment_method') === 'bre_b' ? 'border-primary bg-primary-subtle' : '' }}" style="transition: all 0.2s ease;">
                            <input class="form-check-input ms-0 me-3" type="radio" name="payment_method" id="pay_bre_b" value="bre_b" {{ old('payment_method') === 'bre_b' ? 'checked' : '' }} onchange="updatePaymentSelection()">
                            <label class="form-check-label w-100 d-flex justify-content-between align-items-center cursor-pointer" for="pay_bre_b">
                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <strong class="text-dark fs-6">Bre-B (@ALM143)</strong>
                                        <span class="badge bg-primary rounded-pill px-2.5 py-1 small fw-bold">Transferencia Rápida</span>
                                    </div>
                                    <small class="text-muted d-block mt-1">Transferencia instantánea interoperable desde cualquier banco o billetera (Bancolombia, Nequi, Daviplata, Dale, etc.) usando la llave Bre-B.</small>
                                </div>
                                <div class="fs-3 text-primary ps-3">
                                    <i class="bi bi-qr-code-scan"></i>
                                </div>
                            </label>

                            <!-- Bre-B Instructions Box -->
                            <div id="breBDetails" class="mt-3 pt-3 border-top {{ old('payment_method') === 'bre_b' ? '' : 'd-none' }}">
                                <div class="card bg-white border rounded-3 p-3 shadow-sm">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                        <div>
                                            <div class="text-muted small fw-bold text-uppercase">Llave / Identificador Bre-B:</div>
                                            <div class="fs-4 fw-extrabold text-primary font-monospace">@ALM143</div>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary rounded-pill px-3 py-1.5 btn-sm fw-bold" onclick="navigator.clipboard.writeText('@ALM143'); alert('¡Llave Bre-B @ALM143 copiada al portapapeles!');">
                                            <i class="bi bi-clipboard-check me-1"></i> Copiar Llave @ALM143
                                        </button>
                                    </div>
                                    <div class="alert alert-info py-2 px-3 small rounded-3 mb-0 mt-3 border-0">
                                        <i class="bi bi-info-circle-fill me-1"></i> Transfiere el total exacto de tu pedido a la llave <strong>@ALM143</strong> desde tu app bancaria y confirma tu compra para registrar tu despacho de inmediato.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Order Summary Sidebar -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 sticky-top" style="top: 90px;">
                    <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">Tu Pedido ({{ count($items) }} items)</h5>

                    <!-- Items list -->
                    <div class="d-flex flex-column gap-3 mb-4 overflow-auto pe-1" style="max-height: 240px;">
                        @foreach($items as $item)
                            <div class="d-flex gap-3 align-items-center">
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="rounded-3 object-fit-cover shadow-sm" style="width: 50px; height: 50px;">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 small fw-bold text-truncate" style="max-width: 170px;">{{ $item['name'] }}</h6>
                                    <div class="text-muted small">{{ $item['quantity'] }} x {{ format_cop($item['price']) }}</div>
                                </div>
                                <span class="small fw-bold text-dark font-monospace">{{ format_cop($item['total']) }}</span>
                            </div>
                        @endforeach
                    </div>

                    <hr>

                    <!-- Breakdown -->
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Subtotal:</span>
                        <span class="fw-semibold font-monospace">{{ format_cop($subtotal) }}</span>
                    </div>

                    @if($discount > 0)
                        <div class="d-flex justify-content-between mb-2 small text-success">
                            <span>Descuento ({{ $coupon->code }}):</span>
                            <span class="fw-semibold font-monospace">-{{ format_cop($discount) }}</span>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Costo de Envío:</span>
                        @if($shipping == 0)
                            <span class="text-success fw-bold">GRATIS</span>
                        @else
                            <span class="fw-semibold font-monospace">{{ format_cop($shipping) }}</span>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between mb-3 small">
                        <span class="text-muted">IVA (19%):</span>
                        <span class="fw-semibold font-monospace">{{ format_cop($tax) }}</span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fs-5 fw-bold text-dark">Total a Pagar:</span>
                        <span class="fs-4 fw-extrabold text-primary font-monospace">{{ format_cop($total) }}</span>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm py-3" id="submitOrderBtn">
                            <i class="bi bi-shield-check me-2"></i> Confirmar Pedido
                        </button>
                    </div>

                    <div class="text-center mt-3 small text-muted">
                        <i class="bi bi-lock-fill text-success me-1"></i> Transacción 100% Cifrada y Segura (COP)
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function updatePaymentSelection() {
        const breBDetails = document.getElementById('breBDetails');
        const isBreB = document.getElementById('pay_bre_b') && document.getElementById('pay_bre_b').checked;

        if (breBDetails) {
            if (isBreB) {
                breBDetails.classList.remove('d-none');
            } else {
                breBDetails.classList.add('d-none');
            }
        }

        document.querySelectorAll('.payment-option').forEach(option => {
            const radio = option.querySelector('input[type=radio]');
            if (radio && radio.checked) {
                option.classList.add('border-primary', 'bg-primary-subtle');
            } else {
                option.classList.remove('border-primary', 'bg-primary-subtle');
            }
        });
    }

    document.getElementById('checkoutForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitOrderBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Procesando tu Pedido...';
    });
</script>
@endpush
