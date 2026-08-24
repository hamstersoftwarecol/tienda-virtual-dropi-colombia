@extends('layouts.app')

@section('title', 'Finalizar Compra - Checkout')

@section('content')
<div class="container py-5">
    <div class="mb-4">
        <h2 class="fw-bold mb-1 text-dark"><i class="bi bi-credit-card text-primary me-2"></i>Finalizar Compra</h2>
        <p class="text-muted small">Por favor completa tus datos de entrega en Colombia y selecciona tu método de pago preferido (Precios en COP).</p>
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
                            <input type="text" name="customer_name" id="customer_name" class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name', $user?->name) }}" required placeholder="Ej: Carlos Mendoza">
                            @error('customer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="recipient_dni" class="form-label fw-semibold small">Cédula de Ciudadanía / DNI (Para Guía de Envío) *</label>
                            <input type="text" name="recipient_dni" id="recipient_dni" class="form-control @error('recipient_dni') is-invalid @enderror" value="{{ old('recipient_dni', $user?->dni) }}" required placeholder="Ej: 1020456789">
                            @error('recipient_dni')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="customer_email" class="form-label fw-semibold small">Correo Electrónico *</label>
                            <input type="email" name="customer_email" id="customer_email" class="form-control @error('customer_email') is-invalid @enderror" value="{{ old('customer_email', $user?->email) }}" required placeholder="ejemplo@correo.com">
                            @error('customer_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="customer_phone" class="form-label fw-semibold small">Celular / WhatsApp *</label>
                            <input type="tel" name="customer_phone" id="customer_phone" class="form-control @error('customer_phone') is-invalid @enderror" value="{{ old('customer_phone', $user?->phone) }}" required placeholder="+57 300 123 4567">
                            @error('customer_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="shipping_department" class="form-label fw-semibold small">Departamento *</label>
                            <input type="text" name="shipping_department" id="shipping_department" class="form-control @error('shipping_department') is-invalid @enderror" value="{{ old('shipping_department', $user?->department ?: 'Cundinamarca') }}" required placeholder="Ej: Antioquia, Cundinamarca, Valle...">
                            @error('shipping_department')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="shipping_city" class="form-label fw-semibold small">Ciudad / Municipio *</label>
                            <input type="text" name="shipping_city" id="shipping_city" class="form-control @error('shipping_city') is-invalid @enderror" value="{{ old('shipping_city', $user?->city) }}" required placeholder="Ej: Bogotá D.C., Medellín, Cali...">
                            @error('shipping_city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-8">
                            <label for="shipping_address" class="form-label fw-semibold small">Dirección Exacta de Entrega *</label>
                            <input type="text" name="shipping_address" id="shipping_address" class="form-control @error('shipping_address') is-invalid @enderror" value="{{ old('shipping_address', $user?->address) }}" required placeholder="Carrera, Calle, Número, Apto o Barrio">
                            @error('shipping_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="shipping_carrier" class="form-label fw-semibold small">Transportadora</label>
                            <select name="shipping_carrier" id="shipping_carrier" class="form-select">
                                <option value="Coordinadora" selected>Coordinadora</option>
                                <option value="Servientrega">Servientrega</option>
                                <option value="Interrapidisimo">Interrapidísimo</option>
                                <option value="Envia">Envía</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="shipping_postal_code" class="form-label fw-semibold small">Código Postal (Opcional)</label>
                            <input type="text" name="shipping_postal_code" id="shipping_postal_code" class="form-control" value="{{ old('shipping_postal_code', $user?->postal_code) }}" placeholder="110111">
                        </div>

                        <div class="col-12">
                            <label for="order_notes" class="form-label fw-semibold small">Indicaciones para la Entrega (Opcional)</label>
                            <textarea name="order_notes" id="order_notes" rows="2" class="form-control" placeholder="Ej: Portería 2, conjunto residencial, timbre 301...">{{ old('order_notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Payment Method Selection -->
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                    <h5 class="fw-bold mb-4 text-dark border-bottom pb-2">
                        <span class="badge bg-primary rounded-circle me-2">2</span> Método de Pago
                    </h5>

                    <div class="d-flex flex-column gap-3 mb-4">
                        <!-- PSE Option -->
                        <div class="form-check p-3 border rounded-3 payment-option {{ old('payment_method', 'pse') === 'pse' ? 'border-primary bg-primary-subtle' : '' }}">
                            <input class="form-check-input ms-0 me-3" type="radio" name="payment_method" id="pay_pse" value="pse" {{ old('payment_method', 'pse') === 'pse' ? 'checked' : '' }} onchange="updatePaymentSelection()">
                            <label class="form-check-label w-100 d-flex justify-content-between align-items-center cursor-pointer" for="pay_pse">
                                <div>
                                    <strong class="d-block text-dark">PSE (Pago Seguro en Línea)</strong>
                                    <small class="text-muted">Débito directo desde tu cuenta Bancolombia, Davivienda, BBVA, etc.</small>
                                </div>
                                <span class="badge bg-primary px-3 py-2 fw-bold">PSE</span>
                            </label>
                        </div>

                        <!-- Nequi / Daviplata Option -->
                        <div class="form-check p-3 border rounded-3 payment-option {{ old('payment_method') === 'nequi' ? 'border-primary bg-primary-subtle' : '' }}">
                            <input class="form-check-input ms-0 me-3" type="radio" name="payment_method" id="pay_nequi" value="nequi" {{ old('payment_method') === 'nequi' ? 'checked' : '' }} onchange="updatePaymentSelection()">
                            <label class="form-check-label w-100 d-flex justify-content-between align-items-center cursor-pointer" for="pay_nequi">
                                <div>
                                    <strong class="d-block text-dark">Nequi / Daviplata</strong>
                                    <small class="text-muted">Paga al instante escaneando código QR o número de celular</small>
                                </div>
                                <div class="fs-4 text-info">
                                    <i class="bi bi-phone-fill"></i>
                                </div>
                            </label>
                        </div>

                        <!-- Credit Card Option -->
                        <div class="form-check p-3 border rounded-3 payment-option {{ old('payment_method') === 'credit_card' ? 'border-primary bg-primary-subtle' : '' }}">
                            <input class="form-check-input ms-0 me-3" type="radio" name="payment_method" id="pay_cc" value="credit_card" {{ old('payment_method') === 'credit_card' ? 'checked' : '' }} onchange="updatePaymentSelection()">
                            <label class="form-check-label w-100 d-flex justify-content-between align-items-center cursor-pointer" for="pay_cc">
                                <div>
                                    <strong class="d-block text-dark">Tarjeta de Crédito o Débito</strong>
                                    <small class="text-muted">Visa, MasterCard, American Express, Diners Club</small>
                                </div>
                                <div class="fs-4 text-secondary">
                                    <i class="bi bi-credit-card-2-front"></i>
                                </div>
                            </label>
                            <!-- Mock Card Inputs -->
                            <div id="creditCardDetails" class="mt-3 pt-3 border-top {{ old('payment_method') === 'credit_card' ? '' : 'd-none' }}">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <input type="text" class="form-control form-control-sm" placeholder="Número de Tarjeta (Simulado: 4532 •••• •••• 8890)" value="4532 •••• •••• 8890" readonly>
                                    </div>
                                    <div class="col-6">
                                        <input type="text" class="form-control form-control-sm" placeholder="MM/AA" value="12/28" readonly>
                                    </div>
                                    <div class="col-6">
                                        <input type="text" class="form-control form-control-sm" placeholder="CVV" value="•••" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cash on Delivery Option -->
                        <div class="form-check p-3 border rounded-3 payment-option {{ old('payment_method') === 'cash_on_delivery' ? 'border-primary bg-primary-subtle' : '' }}">
                            <input class="form-check-input ms-0 me-3" type="radio" name="payment_method" id="pay_cod" value="cash_on_delivery" {{ old('payment_method') === 'cash_on_delivery' ? 'checked' : '' }} onchange="updatePaymentSelection()">
                            <label class="form-check-label w-100 d-flex justify-content-between align-items-center cursor-pointer" for="pay_cod">
                                <div>
                                    <strong class="d-block text-dark">Pago Contra Entrega</strong>
                                    <small class="text-muted">Paga en efectivo al transportador al recibir tu paquete en casa</small>
                                </div>
                                <div class="fs-4 text-success">
                                    <i class="bi bi-cash-stack"></i>
                                </div>
                            </label>
                        </div>

                        <!-- Bank Transfer -->
                        <div class="form-check p-3 border rounded-3 payment-option {{ old('payment_method') === 'bank_transfer' ? 'border-primary bg-primary-subtle' : '' }}">
                            <input class="form-check-input ms-0 me-3" type="radio" name="payment_method" id="pay_bank" value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'checked' : '' }} onchange="updatePaymentSelection()">
                            <label class="form-check-label w-100 d-flex justify-content-between align-items-center cursor-pointer" for="pay_bank">
                                <div>
                                    <strong class="d-block text-dark">Transferencia Bancolombia / Davivienda</strong>
                                    <small class="text-muted">Te enviaremos los datos de cuenta corriente/ahorros</small>
                                </div>
                                <div class="fs-4 text-dark">
                                    <i class="bi bi-bank"></i>
                                </div>
                            </label>
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
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="rounded-3 object-fit-cover" style="width: 50px; height: 50px;">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 small fw-bold text-truncate" style="max-width: 170px;">{{ $item['name'] }}</h6>
                                    <div class="text-muted small">{{ $item['quantity'] }} x {{ format_cop($item['price']) }}</div>
                                </div>
                                <span class="small fw-bold text-dark">{{ format_cop($item['total']) }}</span>
                            </div>
                        @endforeach
                    </div>

                    <hr>

                    <!-- Breakdown -->
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Subtotal:</span>
                        <span class="fw-semibold">{{ format_cop($subtotal) }}</span>
                    </div>

                    @if($discount > 0)
                        <div class="d-flex justify-content-between mb-2 small text-success">
                            <span>Descuento ({{ $coupon->code }}):</span>
                            <span class="fw-semibold">-{{ format_cop($discount) }}</span>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Costo de Envío:</span>
                        @if($shipping == 0)
                            <span class="text-success fw-bold">GRATIS</span>
                        @else
                            <span class="fw-semibold">{{ format_cop($shipping) }}</span>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between mb-3 small">
                        <span class="text-muted">IVA (19%):</span>
                        <span class="fw-semibold">{{ format_cop($tax) }}</span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fs-5 fw-bold text-dark">Total a Pagar:</span>
                        <span class="fs-4 fw-extrabold text-primary">{{ format_cop($total) }}</span>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm py-3" id="submitOrderBtn">
                            <i class="bi bi-shield-check me-2"></i> Confirmar y Pagar Pedido
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
        const ccDetails = document.getElementById('creditCardDetails');
        const isCC = document.getElementById('pay_cc') && document.getElementById('pay_cc').checked;
        if (ccDetails) {
            if (isCC) {
                ccDetails.classList.remove('d-none');
            } else {
                ccDetails.classList.add('d-none');
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
