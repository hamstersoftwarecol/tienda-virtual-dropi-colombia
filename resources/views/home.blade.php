@extends('layouts.app')

@section('title', 'Inicio')
@section('meta_description', 'Tu tienda virtual premium en Colombia. Encuentra tecnología, gadgets, hogar y moda con envíos rápidos a todo el país y pago contra entrega seguro.')

@section('content')
<!-- ========================================================================= -->
<!-- 1. Hero Section: Modern, Dynamic & Product-Centric (Bootstrap 5) -->
<!-- ========================================================================= -->
<section class="hero-wrapper py-3 py-lg-4">
    <div class="container">
        <div class="hero-card border-0 rounded-4 overflow-hidden shadow-lg p-4 p-md-5 position-relative" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 60%, #312e81 100%); color: #ffffff;">
            <!-- Ambient Glow Backgrounds -->
            <div class="position-absolute top-0 end-0 translate-middle-y rounded-circle opacity-25" style="width: 450px; height: 450px; background: radial-gradient(circle, #4f46e5 0%, transparent 70%); filter: blur(40px); pointer-events: none;"></div>
            <div class="position-absolute bottom-0 start-0 translate-middle rounded-circle opacity-20" style="width: 350px; height: 350px; background: radial-gradient(circle, #0ea5e9 0%, transparent 70%); filter: blur(40px); pointer-events: none;"></div>

            <div class="row align-items-center g-4 g-lg-5 position-relative" style="z-index: 2;">
                <!-- Left: Catchy Copy & Fast Search -->
                <div class="col-lg-7">
                    <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-10 border border-white border-opacity-20 text-white rounded-pill px-3 py-1.5 mb-3 backdrop-blur shadow-sm">
                        <span class="fs-6">🇨🇴</span>
                        <span class="small fw-semibold">Envíos a Toda Colombia &bull; Pago Contra Entrega</span>
                        <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.7rem;">2026</span>
                    </div>

                    <h1 class="display-5 fw-extrabold text-white mb-3 tracking-tight lh-sm">
                        Los Mejores Productos <br class="d-none d-sm-block">
                        <span style="background: linear-gradient(135deg, #38bdf8 0%, #818cf8 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                            Directo a tu Puerta en Colombia
                        </span>
                    </h1>

                    <p class="lead text-light text-opacity-80 mb-4" style="max-width: 540px; font-size: 1.05rem;">
                        Descubre novedades en tecnología, audio, hogar y cuidado personal. Paga en efectivo al recibir o con <strong>PSE, Nequi y Daviplata</strong>.
                    </p>

                    <!-- Integrated Search Bar -->
                    <div class="card bg-white p-2 rounded-pill shadow mb-4 border-0" style="max-width: 520px;">
                        <form action="{{ route('shop.index') }}" method="GET" class="d-flex align-items-center">
                            <span class="ps-3 text-muted"><i class="bi bi-search fs-5"></i></span>
                            <input type="text" name="q" class="form-control border-0 shadow-none px-3 text-dark bg-transparent" placeholder="¿Qué estás buscando hoy? (ej. reloj, audio, cepillo)...">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold flex-shrink-0 shadow-sm">
                                Buscar
                            </button>
                        </form>
                    </div>

                    <!-- Quick Trend Badges -->
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-4 small">
                        <span class="text-light text-opacity-60"><i class="bi bi-fire text-warning me-1"></i>Tendencias:</span>
                        <a href="{{ route('shop.index', ['q' => 'reloj']) }}" class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-15 text-decoration-none rounded-pill px-3 py-1.5 hover-scale">Relojes</a>
                        <a href="{{ route('shop.index', ['q' => 'audifonos']) }}" class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-15 text-decoration-none rounded-pill px-3 py-1.5 hover-scale">Audífonos</a>
                        <a href="{{ route('shop.index', ['q' => 'vapor']) }}" class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-15 text-decoration-none rounded-pill px-3 py-1.5 hover-scale">Mascotas & Hogar</a>
                        <a href="{{ route('shop.index', ['on_sale' => 1]) }}" class="badge bg-danger text-white text-decoration-none rounded-pill px-3 py-1.5 hover-scale">⚡ Ofertas</a>
                    </div>

                    <!-- Trust Pillars -->
                    <div class="row g-3 pt-3 border-top border-white border-opacity-15 text-light text-opacity-90 small">
                        <div class="col-4 d-flex align-items-center gap-2">
                            <div class="bg-success bg-opacity-20 text-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-cash-coin fs-5"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-white">Contra Entrega</div>
                                <div class="text-light text-opacity-60" style="font-size: 0.75rem;">Pagas al recibir</div>
                            </div>
                        </div>
                        <div class="col-4 d-flex align-items-center gap-2">
                            <div class="bg-primary bg-opacity-20 text-info rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-truck fs-5"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-white">Envío Nacional</div>
                                <div class="text-light text-opacity-60" style="font-size: 0.75rem;">24 a 48 Horas</div>
                            </div>
                        </div>
                        <div class="col-4 d-flex align-items-center gap-2">
                            <div class="bg-warning bg-opacity-20 text-warning rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-shield-check fs-5"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-white">Garantía 100%</div>
                                <div class="text-light text-opacity-60" style="font-size: 0.75rem;">30 días cambio</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Featured Spotlight Product Card -->
                <div class="col-lg-5">
                    @if($heroSpotlight)
                        <div class="card border-0 rounded-4 bg-white text-dark shadow-2xl overflow-hidden position-relative transform-hover" style="transition: transform 0.3s ease;">
                            <!-- Top Badge -->
                            <div class="position-absolute top-0 start-0 m-3 d-flex flex-column gap-1" style="z-index: 3;">
                                <span class="badge bg-danger rounded-pill px-3 py-1.5 fw-bold shadow-sm">
                                    <i class="bi bi-lightning-fill me-1"></i> PRODUCTO DESTACADO
                                </span>
                                @if($heroSpotlight->discount_percent > 0)
                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-1 fw-bold shadow-sm">
                                        -{{ $heroSpotlight->discount_percent }}% OFF
                                    </span>
                                @endif
                            </div>

                            <!-- Product Image -->
                            <div class="position-relative bg-light text-center p-4 d-flex align-items-center justify-content-center" style="height: 290px;">
                                <a href="{{ route('shop.show', $heroSpotlight->slug) }}">
                                    <img src="{{ $heroSpotlight->image }}" alt="{{ $heroSpotlight->name }}" class="img-fluid rounded-3 object-fit-contain" style="max-height: 260px; max-width: 100%; transition: transform 0.4s ease;">
                                </a>
                            </div>

                            <!-- Card Body -->
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="badge bg-primary-subtle text-primary fw-bold rounded-pill px-2.5 py-1 text-uppercase" style="font-size: 0.7rem;">
                                        {{ $heroSpotlight->category ? $heroSpotlight->category->name : 'General' }}
                                    </span>
                                    <div class="rating-stars small">
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <span class="fw-bold text-dark ms-1">4.9 (1.4k)</span>
                                    </div>
                                </div>

                                <h5 class="fw-bold text-dark mb-2 text-truncate">
                                    <a href="{{ route('shop.show', $heroSpotlight->slug) }}" class="text-dark text-decoration-none">
                                        {{ $heroSpotlight->name }}
                                    </a>
                                </h5>

                                <p class="text-muted small mb-3 text-truncate-2" style="font-size: 0.85rem;">
                                    {{ Str::limit(strip_tags($heroSpotlight->short_description ?: $heroSpotlight->description), 110) }}
                                </p>

                                <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                                    <div>
                                        <div class="text-muted small" style="font-size: 0.75rem;">Precio Especial COP</div>
                                        <div class="d-flex align-items-baseline gap-2">
                                            <span class="fs-4 fw-extrabold text-primary">{{ format_cop($heroSpotlight->price) }}</span>
                                            @if($heroSpotlight->compare_price && $heroSpotlight->compare_price > $heroSpotlight->price)
                                                <span class="text-muted text-decoration-line-through small">{{ format_cop($heroSpotlight->compare_price) }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <form action="{{ route('cart.add') }}" method="POST" class="ajax-add-to-cart">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $heroSpotlight->id }}">
                                        <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-1.5">
                                            <i class="bi bi-cart-plus-fill"></i> Comprar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========================================================================= -->
<!-- 2. Featured Categories Section (Interactive Visual Pills) -->
<!-- ========================================================================= -->
<section class="container py-4 py-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-2">
        <div>
            <span class="text-primary fw-bold small text-uppercase letter-spacing-1 d-flex align-items-center gap-1">
                <i class="bi bi-grid-fill"></i> Explora por Departamento
            </span>
            <h2 class="fw-extrabold mb-0 text-dark">Categorías Destacadas</h2>
        </div>
        <a href="{{ route('shop.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold">
            Ver todas las categorías <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="row g-3 g-md-4">
        @foreach($featuredCategories as $category)
            <div class="col-6 col-md-4 col-lg-3">
                <a href="{{ route('shop.index', ['category' => $category->slug]) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white category-hover-card text-center position-relative overflow-hidden" style="transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
                        <div class="bg-primary-subtle text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 64px; height: 64px;">
                            <i class="bi {{ $category->icon ?: 'bi-box-seam' }} fs-3"></i>
                        </div>
                        <h6 class="fw-bold mb-1 text-dark text-truncate">{{ $category->name }}</h6>
                        <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1 small">
                            {{ $category->products()->count() }} {{ Str::plural('producto', $category->products()->count()) }}
                        </span>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</section>

<!-- ========================================================================= -->
<!-- 3. Interactive Product Hub (Bootstrap 5 Tabs - Destacados / Ofertas / Novedades / Más Vendidos) -->
<!-- ========================================================================= -->
<section class="container py-4 py-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25 rounded-pill px-3 py-1 fw-bold mb-1">
                <i class="bi bi-stars"></i> CATÁLOGO VIRTUAL
            </span>
            <h2 class="fw-extrabold mb-0 text-dark">Explora Nuestros Productos</h2>
        </div>

        <!-- Bootstrap 5 Navigation Pills for Instant Product Filtering -->
        <ul class="nav nav-pills bg-light p-1 rounded-pill border shadow-sm" id="productTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill fw-bold px-3 py-2 btn-sm" id="tab-featured-btn" data-bs-toggle="pill" data-bs-target="#tab-featured" type="button" role="tab" aria-selected="true">
                    <i class="bi bi-star-fill text-warning me-1"></i> Destacados
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill fw-bold px-3 py-2 btn-sm" id="tab-deals-btn" data-bs-toggle="pill" data-bs-target="#tab-deals" type="button" role="tab" aria-selected="false">
                    <i class="bi bi-lightning-charge-fill text-danger me-1"></i> Ofertas Flash
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill fw-bold px-3 py-2 btn-sm" id="tab-new-btn" data-bs-toggle="pill" data-bs-target="#tab-new" type="button" role="tab" aria-selected="false">
                    <i class="bi bi-sparkles text-primary me-1"></i> Novedades
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill fw-bold px-3 py-2 btn-sm" id="tab-bestsellers-btn" data-bs-toggle="pill" data-bs-target="#tab-bestsellers" type="button" role="tab" aria-selected="false">
                    <i class="bi bi-fire text-warning me-1"></i> Más Vendidos
                </button>
            </li>
        </ul>
    </div>

    <!-- Tab Content -->
    <div class="tab-content" id="productTabsContent">
        <!-- Tab 1: Destacados -->
        <div class="tab-pane fade show active" id="tab-featured" role="tabpanel" aria-labelledby="tab-featured-btn">
            <div class="row g-3 g-md-4">
                @forelse($featuredProducts as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                        @include('components.product-card-modern', ['product' => $product])
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-box-seam fs-1 text-muted d-block mb-2"></i>
                        <p class="text-muted">No hay productos destacados en este momento.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Tab 2: Ofertas Flash -->
        <div class="tab-pane fade" id="tab-deals" role="tabpanel" aria-labelledby="tab-deals-btn">
            <div class="row g-3 g-md-4">
                @forelse($bestDeals as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                        @include('components.product-card-modern', ['product' => $product])
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-tag fs-1 text-muted d-block mb-2"></i>
                        <p class="text-muted">No hay ofertas flash activas en este momento.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Tab 3: Novedades -->
        <div class="tab-pane fade" id="tab-new" role="tabpanel" aria-labelledby="tab-new-btn">
            <div class="row g-3 g-md-4">
                @forelse($newArrivals as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                        @include('components.product-card-modern', ['product' => $product])
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-box-seam fs-1 text-muted d-block mb-2"></i>
                        <p class="text-muted">No hay productos nuevos registrados.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Tab 4: Más Vendidos -->
        <div class="tab-pane fade" id="tab-bestsellers" role="tabpanel" aria-labelledby="tab-bestsellers-btn">
            <div class="row g-3 g-md-4">
                @forelse($bestSellers as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                        @include('components.product-card-modern', ['product' => $product])
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-fire fs-1 text-muted d-block mb-2"></i>
                        <p class="text-muted">No hay productos registrados aún.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- View Catalog CTA -->
    <div class="text-center mt-5">
        <a href="{{ route('shop.index') }}" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow">
            <i class="bi bi-grid me-2"></i> Ver Todo el Catálogo de Productos (COP)
        </a>
    </div>
</section>

<!-- ========================================================================= -->
<!-- 4. Flash Deal Countdown Banner (High Conversion Visual) -->
<!-- ========================================================================= -->
<section class="container py-4 py-lg-5">
    <div class="card border-0 rounded-4 p-4 p-md-5 overflow-hidden text-white shadow-xl position-relative" style="background: linear-gradient(135deg, #1e1b4b 0%, #4338ca 60%, #6366f1 100%);">
        <div class="position-absolute top-0 end-0 translate-middle-y opacity-25" style="width: 380px; height: 380px; background: radial-gradient(circle, #f59e0b 0%, transparent 70%); filter: blur(30px);"></div>

        <div class="row align-items-center g-4 position-relative" style="z-index: 2;">
            <div class="col-lg-7">
                <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-extrabold mb-3 d-inline-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-stopwatch-fill"></i> OFERTA RELÁMPAGO DE LA SEMANA
                </span>
                <h2 class="display-6 fw-extrabold text-white mb-3">
                    Hasta 30% de Descuento en Productos Seleccionados
                </h2>
                <p class="lead text-light text-opacity-90 mb-4" style="font-size: 1.05rem;">
                    Aprovecha precios mayoristas con entrega contra entrega. Ingresa el cupón <strong class="bg-white text-dark px-2 py-1 rounded font-monospace fw-bold">NOVA10</strong> y obtén 10% de descuento adicional.
                </p>

                <!-- Live Countdown Timer -->
                <div class="d-flex align-items-center gap-3 mb-4" id="flash-countdown">
                    <div class="text-center bg-white bg-opacity-15 border border-white border-opacity-20 rounded-3 p-2 px-3">
                        <div class="fs-4 fw-extrabold text-warning" id="cd-hours">14</div>
                        <div class="text-uppercase text-light text-opacity-75" style="font-size: 0.65rem;">Horas</div>
                    </div>
                    <div class="fs-4 fw-bold text-warning">:</div>
                    <div class="text-center bg-white bg-opacity-15 border border-white border-opacity-20 rounded-3 p-2 px-3">
                        <div class="fs-4 fw-extrabold text-warning" id="cd-minutes">38</div>
                        <div class="text-uppercase text-light text-opacity-75" style="font-size: 0.65rem;">Minutos</div>
                    </div>
                    <div class="fs-4 fw-bold text-warning">:</div>
                    <div class="text-center bg-white bg-opacity-15 border border-white border-opacity-20 rounded-3 p-2 px-3">
                        <div class="fs-4 fw-extrabold text-warning" id="cd-seconds">45</div>
                        <div class="text-uppercase text-light text-opacity-75" style="font-size: 0.65rem;">Segundos</div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('shop.index', ['on_sale' => 1]) }}" class="btn btn-warning btn-lg rounded-pill px-4 py-2.5 fw-bold text-dark shadow-sm">
                        <i class="bi bi-bag-check-fill me-1"></i> Comprar con Descuento
                    </a>
                    <button type="button" class="btn btn-outline-light btn-lg rounded-pill px-4 py-2.5 fw-semibold" onclick="navigator.clipboard.writeText('NOVA10'); alert('¡Cupón NOVA10 copiado al portapapeles!');">
                        <i class="bi bi-clipboard-check me-1"></i> Copiar Cupón NOVA10
                    </button>
                </div>
            </div>

            <div class="col-lg-5 text-center d-none d-lg-block">
                <i class="bi bi-gift-fill text-white opacity-20" style="font-size: 14rem; line-height: 1;"></i>
            </div>
        </div>
    </div>
</section>

<!-- ========================================================================= -->
<!-- 5. Carrier & Payment Trust Guarantee (Colombia Logistics) -->
<!-- ========================================================================= -->
<section class="container py-4 py-lg-5">
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-md-5">
        <div class="text-center mb-4">
            <span class="text-primary fw-bold small text-uppercase letter-spacing-1">Garantía de Satisfacción</span>
            <h3 class="fw-extrabold text-dark mb-1">¿Por qué Comprar en NovaStore?</h3>
            <p class="text-muted small mb-0">Comercio electrónico seguro, transparente y respaldado en toda Colombia.</p>
        </div>

        <div class="row g-4 text-center">
            <div class="col-md-3 col-6">
                <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex p-3 mb-3 shadow-sm" style="width: 68px; height: 68px; align-items: center; justify-content: center;">
                    <i class="bi bi-box-seam fs-2"></i>
                </div>
                <h6 class="fw-bold mb-1 text-dark">Transportadoras Líderes</h6>
                <p class="small text-muted mb-0">Alianzas con Servientrega, Coordinadora, TCC, Envía e Inter Rapidísimo.</p>
            </div>
            <div class="col-md-3 col-6">
                <div class="bg-success-subtle text-success rounded-circle d-inline-flex p-3 mb-3 shadow-sm" style="width: 68px; height: 68px; align-items: center; justify-content: center;">
                    <i class="bi bi-cash-stack fs-2"></i>
                </div>
                <h6 class="fw-bold mb-1 text-dark">Pago Contra Entrega</h6>
                <p class="small text-muted mb-0">Paga en efectivo al mensajero cuando recibas tu paquete en casa.</p>
            </div>
            <div class="col-md-3 col-6">
                <div class="bg-warning-subtle text-warning rounded-circle d-inline-flex p-3 mb-3 shadow-sm" style="width: 68px; height: 68px; align-items: center; justify-content: center;">
                    <i class="bi bi-shield-check fs-2"></i>
                </div>
                <h6 class="fw-bold mb-1 text-dark">Garantía de 30 Días</h6>
                <p class="small text-muted mb-0">Cuentas con respaldo y cambio garantizado ante cualquier defecto.</p>
            </div>
            <div class="col-md-3 col-6">
                <div class="bg-info-subtle text-info rounded-circle d-inline-flex p-3 mb-3 shadow-sm" style="width: 68px; height: 68px; align-items: center; justify-content: center;">
                    <i class="bi bi-whatsapp fs-2"></i>
                </div>
                <h6 class="fw-bold mb-1 text-dark">Soporte WhatsApp</h6>
                <p class="small text-muted mb-0">Atención en tiempo real y seguimiento continuo de tu despacho.</p>
            </div>
        </div>
    </div>
</section>

<!-- ========================================================================= -->
<!-- 6. Verified Customer Reviews & Social Proof (Colombia) -->
<!-- ========================================================================= -->
<section class="container py-4 py-lg-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <span class="text-primary fw-bold small text-uppercase letter-spacing-1">Clientes Satisfechos</span>
            <h2 class="fw-extrabold mb-0 text-dark">Experiencias Reales de Compra</h2>
        </div>
        <div class="d-none d-md-flex align-items-center gap-1 text-warning fs-5">
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
            <span class="text-dark fs-6 fw-bold ms-1">4.9 / 5.0 (Más de 2.500 entregas)</span>
        </div>
    </div>

    <div class="row g-3 g-md-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 46px; height: 46px;">
                        C
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">Carlos Andrés Méndez</h6>
                        <small class="text-muted"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Bogotá, D.C. &bull; <span class="text-success fw-semibold"><i class="bi bi-check-circle-fill"></i> Comprador Verificado</span></small>
                    </div>
                </div>
                <div class="rating-stars mb-2">
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                </div>
                <p class="text-secondary small mb-0">
                    "El reloj llegó al día siguiente por Coordinadora. Pagué contra entrega sin complicaciones. Excelente calidad y atención al cliente."
                </p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 46px; height: 46px;">
                        M
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">Marcela Trujillo V.</h6>
                        <small class="text-muted"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Medellín, Antioquia &bull; <span class="text-success fw-semibold"><i class="bi bi-check-circle-fill"></i> Comprador Verificado</span></small>
                    </div>
                </div>
                <div class="rating-stars mb-2">
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                </div>
                <p class="text-secondary small mb-0">
                    "El cepillo para mi mascota funciona perfecto con el vapor. Todo empacado impecable. Súper recomendado para comprar en Colombia."
                </p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 46px; height: 46px;">
                        J
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">Julián Restrepo</h6>
                        <small class="text-muted"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Cali, Valle &bull; <span class="text-success fw-semibold"><i class="bi bi-check-circle-fill"></i> Comprador Verificado</span></small>
                    </div>
                </div>
                <div class="rating-stars mb-2">
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                </div>
                <p class="text-secondary small mb-0">
                    "Pagué con Nequi súper rápido y me enviaron la guía de Servientrega de inmediato. Los productos son 100% como se ven en las fotos."
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Dynamic Countdown Script -->
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 24 Hour Countdown Simulator
    let hours = 14, minutes = 38, seconds = 45;
    const hEl = document.getElementById('cd-hours');
    const mEl = document.getElementById('cd-minutes');
    const sEl = document.getElementById('cd-seconds');

    if (hEl && mEl && sEl) {
        setInterval(function() {
            if (seconds > 0) {
                seconds--;
            } else {
                seconds = 59;
                if (minutes > 0) {
                    minutes--;
                } else {
                    minutes = 59;
                    if (hours > 0) hours--;
                }
            }
            hEl.textContent = String(hours).padStart(2, '0');
            mEl.textContent = String(minutes).padStart(2, '0');
            sEl.textContent = String(seconds).padStart(2, '0');
        }, 1000);
    }
});
</script>
@endpush
@endsection
