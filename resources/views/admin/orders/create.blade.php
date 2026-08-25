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
                        <label for="recipient_dni" class="form-label small fw-bold">Cédula de Ciudadanía (C.C.) *</label>
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
                        <input type="text" name="shipping_department" id="shipping_department" list="departmentsList" class="form-control rounded-3" required autocomplete="off" placeholder="Escribe el departamento..." value="{{ old('shipping_department', 'Antioquia') }}">
                        <datalist id="departmentsList"></datalist>
                    </div>

                    <div class="col-md-6">
                        <label for="shipping_city" class="form-label small fw-bold">Ciudad / Municipio de Destino *</label>
                        <input type="text" name="shipping_city" id="shipping_city" list="citiesList" class="form-control rounded-3" required autocomplete="off" placeholder="Escribe la ciudad..." value="{{ old('shipping_city', 'Medellín') }}">
                        <datalist id="citiesList"></datalist>
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

    const colombiaGeography = {
        "Amazonas": ["Leticia", "Puerto Nariño", "El Encanto", "La Chorrera", "La Pedrera", "Puerto Arica", "Puerto Santander", "Tarapacá"],
        "Antioquia": ["Medellín", "Bello", "Itagüí", "Envigado", "Rionegro", "Apartadó", "Turbo", "Caucasia", "Sabaneta", "Caldas", "Copacabana", "La Estrella", "Marinilla", "Guarne", "Santa Fe de Antioquia", "El Carmen de Viboral", "La Ceja", "Chigorodó", "Carepa", "Yarumal", "Segovia", "Santa Rosa de Osos", "Puerto Berrío", "Andes", "Girardota", "Donmatías", "Sonsón", "Urrao", "Ciudad Bolívar", "El Bagre", "San Pedro de los Milagros", "Amagá", "Necoclí", "Remedios", "Dabeiba", "Fredonia", "Valdivia", "Frontino", "San Juan de Urabá", "Jericó", "Jardín", "Titiribí", "Venecia", "Támesis", "Guatapé", "El Peñol", "San Rafael", "San Carlos", "Cocorná", "San Luis", "Puerto Triunfo", "Puerto Nare"],
        "Arauca": ["Arauca", "Saravena", "Tame", "Fortul", "Arauquita", "Cravo Norte", "Puerto Rondón"],
        "Atlántico": ["Barranquilla", "Soledad", "Malambo", "Sabanalarga", "Baranoa", "Galapa", "Puerto Colombia", "Palmar de Varela", "Santo Tomás", "Campo de la Cruz", "Ponedera", "Polonuevo", "Juan de Acosta", "Luruaco", "Repelón", "Usiacurí", "Piojó", "Tubará", "Santa Lucía", "Manatí", "Candelaria", "Suan"],
        "Bogotá D.C.": ["Bogotá D.C.", "Usaquén", "Chapinero", "Santa Fe", "San Cristóbal", "Usme", "Tunjuelito", "Bosa", "Kennedy", "Fontibón", "Engativá", "Suba", "Barrios Unidos", "Teusaquillo", "Los Mártires", "Antonio Nariño", "Puente Aranda", "La Candelaria", "Rafael Uribe Uribe", "Ciudad Bolívar"],
        "Bolívar": ["Cartagena", "Magangué", "El Carmen de Bolívar", "Arjona", "Turbaco", "María La Baja", "San Juan Nepomuceno", "San Jacinto", "Santa Cruz de Mompox", "Santa Rosa del Sur", "San Pablo", "Morales", "Simití", "Cantagallo", "San Jacinto del Cauca", "Achí", "Pinillos", "Tiquisio", "Altos del Rosario", "Barranco de Loba", "Calamar", "Mahates", "Villanueva", "Santa Catalina", "Santa Rosa de Lima", "Soplaviento", "San Estanislao", "Clemencia"],
        "Boyacá": ["Tunja", "Duitama", "Sogamoso", "Chiquinquirá", "Paipa", "Villa de Leyva", "Puerto Boyacá", "Moniquirá", "Nobsa", "Samacá", "Garagoa", "Guateque", "Soatá", "Boavita", "Tibasosa", "Santa Rosa de Viterbo", "Monguí", "Aquitania", "Toca", "Ráquira", "Saboyá", "Ventaquemada", "Arcabuco", "Cómbita", "Siachoque", "Tuta", "Pesca", "Iza", "Firavitoba", "Tota", "Belén", "Socha", "El Cocuy", "Güicán"],
        "Caldas": ["Manizales", "Villamaría", "Chinchiná", "La Dorada", "Riosucio", "Anserma", "Supía", "Salamina", "Neira", "Pensilvania", "Aguadas", "Samaná", "Manzanares", "Palestina", "Marquetalia", "Victoria", "Viterbo", "Belalcázar", "Risaralda", "Filadelfia", "Aranzazu", "Pácora", "Norcasia"],
        "Caquetá": ["Florencia", "San Vicente del Caguán", "Cartagena del Chairá", "Puerto Rico", "El Doncello", "El Paujil", "La Montañita", "Belén de los Andaquíes", "San José del Fragua", "Albania", "Curillo", "Valparaíso", "Solita", "Solano", "Milán", "Morelia"],
        "Casanare": ["Yopal", "Aguazul", "Villanueva", "Tauramena", "Paz de Ariporo", "Monterrey", "Maní", "Hato Corozal", "Trinidad", "Pore", "San Luis de Palenque", "Nunchía", "Sabanalarga", "Támara", "Orocué"],
        "Cauca": ["Popayán", "Santander de Quilichao", "Puerto Tejada", "Patía (El Bordo)", "Bolívar", "Piendamó", "Miranda", "Corinto", "Guachené", "Villa Rica", "Caloto", "Silvia", "Morales", "Timbío", "Buenos Aires", "Caldono", "Toribío", "Jambaló", "Inzá", "Páez (Belalcázar)", "El Tambo", "Cajibío", "Rosas", "La Sierra", "La Vega", "Mercaderes", "Guapi", "Timbiquí", "López de Micay"],
        "Cesar": ["Valledupar", "Aguachica", "Agustín Codazzi", "Bosconia", "Curumaní", "El Copey", "La Jagua de Ibirico", "Chiriguaná", "El Paso", "La Paz", "San Alberto", "San Diego", "San Martín", "Pelaya", "Pailitas", "Astrea", "Becerril", "La Gloria", "Gamarra", "Tamalameque", "Manaure Balcón del Cesar", "Pueblo Bello", "Río de Oro"],
        "Chocó": ["Quibdó", "Istmina", "Condoto", "Tadó", "Bahía Solano", "Nuquí", "Acandí", "Unguía", "Riosucio", "Carmen del Darién", "Juradó", "Bojayá", "Medio Atrato", "Atrato", "Lloró", "Bagadó", "Alto Baudó", "Bajo Baudó (Pizarro)", "San José del Palmar", "Nóvita", "Litoral del San Juan"],
        "Córdoba": ["Montería", "Cereté", "Sahagún", "Lorica", "Montelíbano", "Planeta Rica", "Ciénaga de Oro", "Tierralta", "Chinú", "San Pelayo", "San Antero", "San Bernardo del Viento", "Puerto Escondido", "Moñitos", "Los Córdobas", "Canalete", "Valencia", "Pueblo Nuevo", "San Carlos", "Buenavista", "Ayapel", "La Apartada", "Puerto Libertador", "San José de Uré", "Tuchín", "Momil", "Purísima", "Chimá", "Cotorra"],
        "Cundinamarca": ["Soacha", "Chía", "Zipaquirá", "Facatativá", "Fusagasugá", "Mosquera", "Madrid", "Funza", "Cajicá", "Girardot", "Cota", "Sopó", "Tocancipá", "Tabio", "Tenjo", "Sibaté", "La Calera", "Ubaté", "Villeta", "Silvania", "Tocaima", "Gachancipá", "Chocontá", "Guaduas", "Anapoima", "La Mesa", "Pacho", "Nemocón", "Cogua", "Subachoque", "El Rosal", "Bojacá", "Sesquilé", "Guatavita", "Suesca", "Cucunubá", "Villapinzón", "Guasca", "Choachí", "Fómeque", "Ubaque", "Chipaque", "Cáqueza", "Agua de Dios", "Ricaurte", "La Vega", "Sasaima", "San Francisco", "Puerto Salgar"],
        "Guainía": ["Inírida", "Barranco Minas", "Mapiripana", "San Felipe", "Puerto Colombia", "La Guadalupe"],
        "Guaviare": ["San José del Guaviare", "Calamar", "El Retorno", "Miraflores"],
        "Huila": ["Neiva", "Pitalito", "Garzón", "La Plata", "Campoalegre", "Rivera", "Palermo", "Gigante", "San Agustín", "Algeciras", "Aipe", "Timaná", "Isnos", "Acevedo", "Tarqui", "Tesalia", "Paicol", "Yaguará", "Hobo", "Villavieja", "Baraya", "Tello", "Santa María", "Agrado", "Pital", "Suaza", "Guadalupe", "Altamira"],
        "La Guajira": ["Riohacha", "Maicao", "Uribia", "Manaure", "Fonseca", "San Juan del Cesar", "Barrancas", "Villanueva", "Albania", "Dibulla", "Hatonuevo", "Distracción", "El Molino", "Urumita"],
        "Magdalena": ["Santa Marta", "Ciénaga", "Fundación", "Plato", "El Banco", "Aracataca", "Pivijay", "Zona Bananera", "Santa Ana", "San Sebastián de Buenavista", "Guamal", "El Retén", "Puebloviejo", "Sitio Nuevo", "Remolino", "Salamina", "Concordia", "El Piñón", "Chibolo", "Nueva Granada", "Ariguaní (El Difícil)", "San Ángel"],
        "Meta": ["Villavicencio", "Acacías", "Granada", "Puerto López", "San Martín", "Puerto Gaitán", "Cumaral", "Restrepo", "Guamal", "Vista Hermosa", "La Macarena", "Mesetas", "San Juan de Arama", "Lejanías", "Fuente de Oro", "Castilla la Nueva", "Cubarral", "Puerto Lleras", "Puerto Rico", "Puerto Concordia", "Mapiripán", "Barranca de Upía", "Cabuyaro"],
        "Nariño": ["Pasto", "Tumaco", "Ipiales", "Túquerres", "La Unión", "Samaniego", "Sandoná", "El Charco", "Barbacoas", "Ricaurte", "Cumbal", "Guachucal", "Pupiales", "Aldana", "Contadero", "Gualmatán", "Iles", "Córdoba", "Potosí", "Puerres", "Funes", "Consacá", "Ancuya", "Guaitarilla", "Imués", "Yacuanquer", "Tangua", "Chachagüí", "Buesaco", "San Lorenzo", "La Cruz", "Belén", "San Bernardo", "Linares", "El Peñol", "Los Andes (Sotomayor)", "La Llanada", "Policarpa", "Cumbitara", "Leiva", "Mosquera", "La Tola", "Francisco Pizarro"],
        "Norte de Santander": ["Cúcuta", "Ocaña", "Villa del Rosario", "Los Patios", "Pamplona", "Tibú", "Chinácota", "El Zulia", "Ábrego", "Sardinata", "Convención", "Teorama", "El Tarra", "San Calixto", "Hacarí", "La Playa", "El Carmen", "Cáchira", "La Esperanza", "Salazar de Las Palmas", "Santiago", "Gramalote", "Lourdes", "Arboledas", "Cucutilla", "Bochalema", "Durania", "Ragonvalia", "Herrán", "Labateca", "Toledo", "San Cayetano", "Puerto Santander"],
        "Putumayo": ["Mocoa", "Puerto Asís", "Orito", "Valle del Guamuez (La Hormiga)", "Villagarzón", "Sibundoy", "San Francisco", "Colón", "Santiago", "Puerto Guzmán", "Puerto Caicedo", "Puerto Leguízamo", "San Miguel (La Dorada)"],
        "Quindío": ["Armenia", "Calarcá", "La Tebaida", "Montenegro", "Quimbaya", "Circasia", "Filandia", "Salento", "Pijao", "Génova", "Córdoba", "Buenavista"],
        "Risaralda": ["Pereira", "Dosquebradas", "Santa Rosa de Cabal", "La Virginia", "Belén de Umbría", "Marsella", "Santuario", "Quinchía", "Guática", "Apía", "Mistrató", "Pueblo Rico", "Balboa", "La Celia"],
        "San Andrés y Providencia": ["San Andrés", "Providencia y Santa Catalina"],
        "Santander": ["Bucaramanga", "Floridablanca", "Girón", "Piedecuesta", "Barrancabermeja", "San Gil", "Socorro", "Barbosa", "Cimitarra", "Sabana de Torres", "San Vicente de Chucurí", "Vélez", "Charalá", "Lebrija", "Rionegro", "Puerto Wilches", "Málaga", "Zapatoca", "Oiba", "Curití", "Aratoca", "Barichara", "Villanueva", "Pinchote", "Páramo", "Mogotes", "Capitanejo", "Macaravita", "San José de Miranda", "Cerrito", "Guaca", "Santa Bárbara", "Tona", "Vetas", "California", "Suratá", "Matanza", "El Playón", "Landázuri", "Bolívar", "La Belleza", "Jesús María", "Florián", "Puente Nacional", "Guavatá", "Chipatá", "La Paz", "Contratación", "Simacota", "Los Santos"],
        "Sucre": ["Sincelejo", "Corozal", "San Marcos", "Tolú (Santiago de Tolú)", "San Onofre", "Sampués", "San Pedro", "Sincé", "Majagual", "Sucre", "Guaranda", "Caimito", "San Benito Abad", "La Unión", "Galeras", "Buenavista", "Los Palmitos", "Ovejas", "Chalán", "Colosó", "Morroa", "Toluviejo", "Coveñas", "El Roble"],
        "Tolima": ["Ibagué", "Espinal", "Melgar", "Chaparral", "Líbano", "Mariquita", "Honda", "Guamo", "Flandes", "Fresno", "Purificación", "Saldaña", "Natagaima", "Coyaima", "Armero Guayabal", "Planadas", "Rioblanco", "Ataco", "Ortega", "San Luis", "Rovira", "San Antonio", "Cajamarca", "Piedras", "Alvarado", "Venadillo", "Palocabildo", "Falan", "Casabianca", "Herveo", "Villahermosa", "Ambalema", "Lérida", "Carmen de Apicalá", "Cunday", "Icononzo", "Prado", "Suárez"],
        "Valle del Cauca": ["Cali", "Buenaventura", "Palmira", "Tuluá", "Cartago", "Buga (Guadalajara de Buga)", "Jamundí", "Yumbo", "Candelaria", "Florida", "Pradera", "Zarzal", "Sevilla", "Roldanillo", "Caicedonia", "La Unión", "Ginebra", "Guacarí", "Bugalagrande", "Andalucía", "El Cerrito", "Dagua", "Restrepo", "La Cumbre", "Vijes", "Yotoco", "Calima (El Darién)", "Riofrío", "Trujillo", "Bolívar", "Toro", "Ansermanuevo", "Alcalá", "Ulloa", "Obando", "La Victoria", "Versalles", "El Dovio", "San Pedro"],
        "Vaupés": ["Mitú", "Carurú", "Taraira", "Papunahua", "Yavaraté", "Pacoa"],
        "Vichada": ["Puerto Carreño", "Santa Rosalía", "Cumaribo", "La Primavera"]
    };

    function normalizeText(text) {
        return (text || '').normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim().toLowerCase();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const depInput = document.getElementById('shipping_department');
        const cityInput = document.getElementById('shipping_city');
        const depList = document.getElementById('departmentsList');
        const cityList = document.getElementById('citiesList');

        if (!depInput || !cityInput || !depList || !cityList) return;

        depList.innerHTML = '';
        Object.keys(colombiaGeography).sort().forEach(dep => {
            const opt = document.createElement('option');
            opt.value = dep;
            depList.appendChild(opt);
        });

        function updateCities(selectedDepartment, keepCity = false) {
            cityList.innerHTML = '';
            const normalizedDep = normalizeText(selectedDepartment);
            let matchedKey = Object.keys(colombiaGeography).find(k => normalizeText(k) === normalizedDep);

            if (matchedKey && colombiaGeography[matchedKey]) {
                colombiaGeography[matchedKey].forEach(city => {
                    const opt = document.createElement('option');
                    opt.value = city;
                    cityList.appendChild(opt);
                });
            }
        }

        depInput.addEventListener('input', function() { updateCities(this.value, true); });
        depInput.addEventListener('change', function() { updateCities(this.value, false); });
        updateCities(depInput.value, true);
    });
</script>
@endpush
