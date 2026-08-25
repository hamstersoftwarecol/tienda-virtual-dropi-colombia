@extends('layouts.admin')

@section('title', 'Generar Pedido Manual')
@section('page_header', 'Generar Pedido / Ventas con Detalles de Comprador (Colombia)')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-cart-plus text-primary me-2"></i>Generar Nuevo Pedido</h4>
        <p class="text-muted small mb-0">Crea pedidos directos de WhatsApp, llamadas o ventas presenciales con datos completos del comprador y despacho a Dropi.</p>
    </div>
    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i> Volver a Pedidos
    </a>
</div>

<form action="{{ route('admin.orders.store.manual') }}" method="POST" id="manualOrderForm">
    @csrf
    <div class="row g-4">
        <!-- Left: Customer Information & Delivery Address -->
        <div class="col-lg-7">
            <!-- Section 1: Buyer Information -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                    <h5 class="fw-bold mb-0 text-dark">
                        <span class="badge bg-primary rounded-circle me-2">1</span> Datos del Comprador (Colombia)
                    </h5>
                    @if($customers->isNotEmpty())
                        <div style="max-width: 240px;">
                            <select id="customerSelector" class="form-select form-select-sm rounded-pill" onchange="autofillCustomer(this)">
                                <option value="">-- Cargar Cliente Existente --</option>
                                @foreach($customers as $cust)
                                    <option value="{{ json_encode([
                                        'name' => $cust->name,
                                        'email' => $cust->email,
                                        'phone' => $cust->phone,
                                        'dni' => $cust->dni,
                                        'city' => $cust->city,
                                        'department' => $cust->department,
                                        'address' => $cust->address,
                                        'postal_code' => $cust->postal_code,
                                    ]) }}">
                                        {{ $cust->name }} ({{ $cust->city }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="customer_name" class="form-label small fw-bold">Nombre Completo del Comprador *</label>
                        <input type="text" name="customer_name" id="customer_name" class="form-control rounded-3" required placeholder="Ej: Andrés Felipe Gómez" value="{{ old('customer_name') }}">
                    </div>

                    <div class="col-md-6">
                        <label for="recipient_dni" class="form-label small fw-bold">Cédula de Ciudadanía / DNI *</label>
                        <input type="text" name="recipient_dni" id="recipient_dni" class="form-control rounded-3" required placeholder="Ej: 1020456789" value="{{ old('recipient_dni') }}">
                    </div>

                    <div class="col-md-6">
                        <label for="customer_phone" class="form-label small fw-bold">Celular / WhatsApp *</label>
                        <input type="tel" name="customer_phone" id="customer_phone" class="form-control rounded-3" required placeholder="+57 310 123 4567" value="{{ old('customer_phone') }}">
                    </div>

                    <div class="col-md-6">
                        <label for="customer_email" class="form-label small fw-bold">Correo Electrónico *</label>
                        <input type="email" name="customer_email" id="customer_email" class="form-control rounded-3" required placeholder="comprador@correo.com" value="{{ old('customer_email') }}">
                    </div>

                    <div class="col-md-6">
                        <label for="shipping_department" class="form-label small fw-bold">Departamento de Destino *</label>
                        <input type="text" name="shipping_department" id="shipping_department" class="form-control rounded-3" required placeholder="Ej: Antioquia, Cundinamarca, Valle..." value="{{ old('shipping_department', 'Cundinamarca') }}">
                    </div>

                    <div class="col-md-6">
                        <label for="shipping_city" class="form-label small fw-bold">Ciudad / Municipio de Destino *</label>
                        <input type="text" name="shipping_city" id="shipping_city" class="form-control rounded-3" required placeholder="Ej: Medellín, Bogotá, Cali..." value="{{ old('shipping_city') }}">
                    </div>

                    <div class="col-12">
                        <label for="shipping_address" class="form-label small fw-bold">Dirección Exacta de Entrega *</label>
                        <input type="text" name="shipping_address" id="shipping_address" class="form-control rounded-3" required placeholder="Calle, Carrera, Número, Conjunto, Apto o Barrio" value="{{ old('shipping_address') }}">
                    </div>

                    <div class="col-md-6">
                        <label for="shipping_postal_code" class="form-label small fw-bold">Código Postal (Opcional)</label>
                        <input type="text" name="shipping_postal_code" id="shipping_postal_code" class="form-control rounded-3" placeholder="110111" value="{{ old('shipping_postal_code') }}">
                    </div>

                    <div class="col-md-6">
                        <label for="order_notes" class="form-label small fw-bold">Notas de Entrega (Opcional)</label>
                        <input type="text" name="order_notes" id="order_notes" class="form-control rounded-3" placeholder="Ej: Llamar antes de entregar..." value="{{ old('order_notes') }}">
                    </div>
                </div>
            </div>

            <!-- Section 2: Logistics & Payment -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">
                    <span class="badge bg-primary rounded-circle me-2">2</span> Logística de Despacho & Pago
                </h5>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="shipping_carrier" class="form-label small fw-bold">Transportadora de Envíos *</label>
                        <select name="shipping_carrier" id="shipping_carrier" class="form-select rounded-3" required>
                            <option value="Coordinadora" selected>📦 Coordinadora Mercantil</option>
                            <option value="Servientrega">🚚 Servientrega Colombia</option>
                            <option value="Interrapidisimo">⚡ Interrapidísimo</option>
                            <option value="Envia">📫 Envía Express</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="payment_method" class="form-label small fw-bold">Forma de Pago *</label>
                        <select name="payment_method" id="payment_method" class="form-select rounded-3" required>
                            <option value="cash_on_delivery" selected>💵 Pago Contra Entrega (Efectivo al Recibir)</option>
                            <option value="pse">🏦 PSE / Transferencia Bancaria (Prepagado)</option>
                            <option value="nequi">📱 Nequi / Daviplata (Prepagado)</option>
                            <option value="credit_card">💳 Tarjeta de Crédito / Débito (Prepagado)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Products Selector & Order Summary -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 sticky-top" style="top: 90px;">
                <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">
                    <span class="badge bg-primary rounded-circle me-2">3</span> Artículos del Pedido
                </h5>

                <!-- Product Add Area -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Seleccionar Producto del Catálogo:</label>
                    <div class="d-flex gap-2">
                        <select id="productPicker" class="form-select form-select-sm rounded-3">
                            <option value="">-- Elige un producto --</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}" data-name="{{ $prod->name }}" data-price="{{ $prod->price }}" data-stock="{{ $prod->stock }}" data-sku="{{ $prod->sku }}" data-image="{{ $prod->image }}">
                                    {{ $prod->name }} — {{ format_cop($prod->price) }} (Stock: {{ $prod->stock }})
                                </option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" onclick="addProductToOrder()">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- Selected items container -->
                <div id="orderItemsContainer" class="d-flex flex-column gap-2 mb-3 overflow-auto pe-1" style="max-height: 250px;">
                    <div id="emptyOrderPlaceholder" class="text-center py-4 text-muted small bg-light rounded-3">
                        <i class="bi bi-cart me-1"></i> No has añadido productos al pedido aún.
                    </div>
                </div>

                <hr>

                <!-- Cost Breakdown in COP -->
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Subtotal:</span>
                    <strong id="calcSubtotal" class="text-dark">$ 0 COP</strong>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label text-muted small mb-1">Costo Envío:</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">$</span>
                            <input type="number" name="shipping_cost" id="shipping_cost" value="0" class="form-control text-end" oninput="recalculateTotals()">
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small mb-1">Descuento:</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">$</span>
                            <input type="number" name="discount" id="discount" value="0" class="form-control text-end" oninput="recalculateTotals()">
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">IVA Estimado (19%):</span>
                    <strong id="calcTax" class="text-dark">$ 0 COP</strong>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="fs-5 fw-bold text-dark">Total a Cobrar:</span>
                    <span class="fs-4 fw-extrabold text-primary" id="calcTotal">$ 0 COP</span>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm py-3" id="saveOrderBtn" disabled>
                        <i class="bi bi-check-circle-fill me-2"></i> Crear y Guardar Pedido
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    let orderItems = [];

    function autofillCustomer(select) {
        if (!select.value) return;
        const data = JSON.parse(select.value);
        document.getElementById('customer_name').value = data.name || '';
        document.getElementById('customer_email').value = data.email || '';
        document.getElementById('customer_phone').value = data.phone || '';
        document.getElementById('recipient_dni').value = data.dni || '';
        document.getElementById('shipping_city').value = data.city || '';
        document.getElementById('shipping_department').value = data.department || 'Cundinamarca';
        document.getElementById('shipping_address').value = data.address || '';
        document.getElementById('shipping_postal_code').value = data.postal_code || '';
    }

    function addProductToOrder() {
        const picker = document.getElementById('productPicker');
        const selectedOption = picker.options[picker.selectedIndex];
        if (!picker.value) return;

        const id = picker.value;
        const name = selectedOption.dataset.name;
        const price = parseFloat(selectedOption.dataset.price);
        const image = selectedOption.dataset.image;
        const maxStock = parseInt(selectedOption.dataset.stock);

        // Check if already in order
        const existing = orderItems.find(it => it.id === id);
        if (existing) {
            if (existing.quantity < maxStock) {
                existing.quantity++;
            }
        } else {
            orderItems.push({
                id: id,
                name: name,
                price: price,
                image: image,
                quantity: 1,
                maxStock: maxStock,
            });
        }

        picker.value = '';
        renderOrderItems();
    }

    function removeItem(index) {
        orderItems.splice(index, 1);
        renderOrderItems();
    }

    function changeQty(index, delta) {
        const item = orderItems[index];
        const newQty = item.quantity + delta;
        if (newQty >= 1 && newQty <= item.maxStock) {
            item.quantity = newQty;
            renderOrderItems();
        }
    }

    function renderOrderItems() {
        const container = document.getElementById('orderItemsContainer');
        if (orderItems.length === 0) {
            container.innerHTML = `
                <div id="emptyOrderPlaceholder" class="text-center py-4 text-muted small bg-light rounded-3">
                    <i class="bi bi-cart me-1"></i> No has añadido productos al pedido aún.
                </div>
            `;
            document.getElementById('saveOrderBtn').disabled = true;
            recalculateTotals();
            return;
        }

        document.getElementById('saveOrderBtn').disabled = false;
        let html = '';
        orderItems.forEach((item, index) => {
            html += `
                <div class="p-2 bg-light rounded-3 border d-flex align-items-center gap-2">
                    <img src="${item.image}" class="rounded-2 object-fit-cover" style="width: 44px; height: 44px;">
                    <div class="flex-grow-1 overflow-hidden">
                        <h6 class="mb-0 small fw-bold text-truncate">${item.name}</h6>
                        <span class="text-muted small">$ ${Math.round(item.price).toLocaleString('es-CO')} c/u</span>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <button type="button" class="btn btn-sm btn-light border p-1" style="width: 26px; height: 26px;" onclick="changeQty(${index}, -1)">-</button>
                        <span class="fw-bold small px-1">${item.quantity}</span>
                        <button type="button" class="btn btn-sm btn-light border p-1" style="width: 26px; height: 26px;" onclick="changeQty(${index}, 1)">+</button>
                    </div>
                    <button type="button" class="btn btn-sm text-danger border-0" onclick="removeItem(${index})"><i class="bi bi-trash"></i></button>

                    <input type="hidden" name="products[${index}][id]" value="${item.id}">
                    <input type="hidden" name="products[${index}][quantity]" value="${item.quantity}">
                    <input type="hidden" name="products[${index}][custom_price]" value="${item.price}">
                </div>
            `;
        });

        container.innerHTML = html;
        recalculateTotals();
    }

    function recalculateTotals() {
        let subtotal = 0;
        orderItems.forEach(it => {
            subtotal += (it.price * it.quantity);
        });

        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const shipping = parseFloat(document.getElementById('shipping_cost').value) || 0;
        const taxable = Math.max(0, subtotal - discount);
        const tax = Math.round(taxable * 0.19);
        const total = taxable + shipping + tax;

        document.getElementById('calcSubtotal').textContent = '$ ' + Math.round(subtotal).toLocaleString('es-CO') + ' COP';
        document.getElementById('calcTax').textContent = '$ ' + Math.round(tax).toLocaleString('es-CO') + ' COP';
        document.getElementById('calcTotal').textContent = '$ ' + Math.round(total).toLocaleString('es-CO') + ' COP';
    }
</script>
@endpush
