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

                        <!-- Department with Interactive Autocomplete Suggestions -->
                        <div class="col-md-6">
                            <label for="shipping_department" class="form-label fw-semibold small">Departamento *</label>
                            <input type="text" name="shipping_department" id="shipping_department" list="departmentsList" class="form-control rounded-3 @error('shipping_department') is-invalid @enderror" value="{{ old('shipping_department', $user?->department ?: 'Antioquia') }}" required autocomplete="off" placeholder="Escribe tu departamento (ej: Antioquia, Cundinamarca)...">
                            <datalist id="departmentsList"></datalist>
                            <div class="form-text text-muted" style="font-size: 0.75rem;"><i class="bi bi-geo-alt text-primary me-1"></i>Escribe para ver sugerencias automáticas</div>
                            @error('shipping_department')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- City with Dynamic Suggestions Based on Department -->
                        <div class="col-md-6">
                            <label for="shipping_city" class="form-label fw-semibold small">Ciudad / Municipio *</label>
                            <input type="text" name="shipping_city" id="shipping_city" list="citiesList" class="form-control rounded-3 @error('shipping_city') is-invalid @enderror" value="{{ old('shipping_city', $user?->city ?: 'Medellín') }}" required autocomplete="off" placeholder="Escribe tu ciudad (ej: Medellín, Bogotá, Cali)...">
                            <datalist id="citiesList"></datalist>
                            <div class="form-text text-muted" style="font-size: 0.75rem;"><i class="bi bi-buildings text-primary me-1"></i>Sugerencias según el departamento seleccionado</div>
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
    // =========================================================================
    // Colombia Geography Autocomplete & Interactive Suggestions
    // =========================================================================
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

    function initColombiaGeography() {
        const depInput = document.getElementById('shipping_department');
        const cityInput = document.getElementById('shipping_city');
        const depList = document.getElementById('departmentsList');
        const cityList = document.getElementById('citiesList');

        if (!depInput || !cityInput || !depList || !cityList) return;

        // Populate Department datalist
        depList.innerHTML = '';
        Object.keys(colombiaGeography).sort().forEach(dep => {
            const opt = document.createElement('option');
            opt.value = dep;
            depList.appendChild(opt);
        });

        // Function to update cities suggestions based on selected department
        function updateCities(selectedDepartment, keepCurrentCity = false) {
            cityList.innerHTML = '';
            const normalizedDep = normalizeText(selectedDepartment);
            
            // Find matched key
            let matchedKey = Object.keys(colombiaGeography).find(k => normalizeText(k) === normalizedDep);
            
            if (matchedKey && colombiaGeography[matchedKey]) {
                colombiaGeography[matchedKey].forEach(city => {
                    const opt = document.createElement('option');
                    opt.value = city;
                    cityList.appendChild(opt);
                });
            } else {
                // If department not explicitly matched, populate with top capital cities across Colombia
                const allCities = [];
                Object.values(colombiaGeography).forEach(cities => {
                    allCities.push(...cities.slice(0, 3));
                });
                [...new Set(allCities)].sort().forEach(city => {
                    const opt = document.createElement('option');
                    opt.value = city;
                    cityList.appendChild(opt);
                });
            }

            if (!keepCurrentCity && matchedKey) {
                // Check if current city belongs to new department
                const currentCity = cityInput.value.trim();
                const belongs = colombiaGeography[matchedKey].some(c => normalizeText(c) === normalizeText(currentCity));
                if (!belongs && currentCity !== '') {
                    cityInput.value = colombiaGeography[matchedKey][0] || '';
                }
            }
        }

        // Event listener for Department input
        depInput.addEventListener('input', function() {
            updateCities(this.value, true);
        });

        depInput.addEventListener('change', function() {
            updateCities(this.value, false);
        });

        // Event listener for City input (auto-fill department if user types a known city)
        cityInput.addEventListener('change', function() {
            const cityNorm = normalizeText(this.value);
            if (!cityNorm) return;

            for (const [dep, cities] of Object.entries(colombiaGeography)) {
                if (cities.some(c => normalizeText(c) === cityNorm)) {
                    if (normalizeText(depInput.value) !== normalizeText(dep)) {
                        depInput.value = dep;
                        updateCities(dep, true);
                    }
                    break;
                }
            }
        });

        // Initial setup
        updateCities(depInput.value, true);
    }

    // Initialize on DOM Ready
    document.addEventListener('DOMContentLoaded', function() {
        initColombiaGeography();
    });

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
