@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
<!-- Hero Section (Light Mode) -->
<div class="container py-4">
    <div class="hero-banner p-4 p-md-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="badge bg-primary-subtle text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill fw-bold mb-3">
                    <i class="bi bi-stars me-1 text-warning"></i> NUEVA COLECCIÓN 2026 COLOMBIA
                </span>
                <h1 class="display-4 fw-extrabold text-dark mb-3 tracking-tight">
                    Tecnología, Estilo y Confort <br>
                    <span class="text-primary">Envíos a Toda Colombia</span>
                </h1>
                <p class="lead text-secondary mb-4" style="max-width: 540px;">
                    Encuentra las mejores marcas en pesos colombianos (COP). Disfruta de envío gratis en compras mayores a <strong>$ 150.000 COP</strong> y pago contra entrega seguro.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('shop.index') }}" class="btn btn-primary btn-lg rounded-pill px-4 py-2 fw-bold shadow-sm">
                        <i class="bi bi-bag-fill me-2"></i> Explorar Catálogo
                    </a>
                    <a href="{{ route('shop.index', ['on_sale' => 1]) }}" class="btn btn-outline-primary btn-lg rounded-pill px-4 py-2 fw-semibold">
                        <i class="bi bi-tag-fill me-2 text-danger"></i> Ver Ofertas Especiales
                    </a>
                </div>

                <!-- Trust Badges -->
                <div class="row g-3 mt-4 pt-3 border-top border-secondary border-opacity-10 text-secondary small">
                    <div class="col-sm-4 d-flex align-items-center gap-2">
                        <i class="bi bi-truck text-primary fs-4"></i>
                        <span class="fw-semibold">Envíos a todo el país</span>
                    </div>
                    <div class="col-sm-4 d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-success fs-4"></i>
                        <span class="fw-semibold">PSE, Nequi y Tarjetas</span>
                    </div>
                    <div class="col-sm-4 d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-repeat text-info fs-4"></i>
                        <span class="fw-semibold">30 Días de Garantía</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block text-center position-relative">
                <div class="position-relative d-inline-block">
                    <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=700&auto=format&fit=crop&q=80" alt="Hero Product" class="img-fluid rounded-4 shadow-sm" style="max-height: 380px; object-fit: cover; border: 4px solid #ffffff;">
                    <!-- Floating Promo Card -->
                    <div class="card position-absolute bottom-0 start-0 translate-middle-y bg-white text-dark shadow-lg rounded-3 border-0 p-2 ms-n3" style="max-width: 210px;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-danger text-white rounded-2 p-2">
                                <i class="bi bi-percent fs-5"></i>
                            </div>
                            <div class="text-start">
                                <div class="fw-bold small">Hasta 30% OFF</div>
                                <div class="text-muted" style="font-size: 0.75rem;">En tecnología y audio COP</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Featured Categories Section -->
<section class="container py-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <span class="text-primary fw-bold small text-uppercase letter-spacing-1">Explora por Categoría</span>
            <h2 class="fw-bold mb-0 text-dark">Categorías Destacadas</h2>
        </div>
        <a href="{{ route('shop.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold">
            Ver todas <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="row g-3 g-md-4">
        @foreach($featuredCategories as $category)
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('shop.index', ['category' => $category->slug]) }}" class="text-decoration-none text-dark">
                    <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100 bg-white category-hover-card" style="transition: all 0.3s ease;">
                        <div class="bg-primary-subtle text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="bi {{ $category->icon ?: 'bi-tag' }} fs-3"></i>
                        </div>
                        <h6 class="fw-bold mb-1 text-truncate">{{ $category->name }}</h6>
                        <small class="text-muted">{{ $category->products()->count() }} productos</small>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</section>

<!-- Featured Products Section -->
<section class="container py-4">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <span class="text-primary fw-bold small text-uppercase letter-spacing-1">Selección Exclusiva</span>
            <h2 class="fw-bold mb-0 text-dark">Productos Destacados</h2>
        </div>
        <a href="{{ route('shop.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold">
            Ver Catálogo <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="row g-4">
        @foreach($featuredProducts as $product)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card product-card">
                    <!-- Image Wrapper -->
                    <div class="product-img-wrapper">
                        <a href="{{ route('shop.show', $product->slug) }}">
                            <img src="{{ $product->image }}" alt="{{ $product->name }}" loading="lazy">
                        </a>
                        @if($product->badge)
                            <span class="product-badge bg-dark text-white">{{ $product->badge }}</span>
                        @elseif($product->discount_percent > 0)
                            <span class="product-badge badge-discount">-{{ $product->discount_percent }}%</span>
                        @endif

                        <!-- Quick Actions Overlay -->
                        <div class="quick-actions">
                            <form action="{{ route('cart.add') }}" method="POST" class="ajax-add-to-cart">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 shadow-sm d-flex align-items-center justify-content-center gap-2 fw-semibold">
                                    <i class="bi bi-cart-plus-fill"></i> Agregar
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="card-body p-3 d-flex flex-column">
                        <div class="small text-muted mb-1">{{ $product->category ? $product->category->name : 'General' }}</div>
                        <h6 class="fw-bold mb-2">
                            <a href="{{ route('shop.show', $product->slug) }}" class="text-dark text-decoration-none text-truncate d-block" title="{{ $product->name }}">
                                {{ $product->name }}
                            </a>
                        </h6>

                        <!-- Rating -->
                        <div class="d-flex align-items-center gap-1 mb-2">
                            <div class="rating-stars">
                                <i class="bi bi-star-fill text-warning"></i>
                                <span class="fw-bold small text-dark ms-1">{{ number_format($product->rating, 1) }}</span>
                            </div>
                            <span class="text-muted small">({{ $product->reviews_count }})</span>
                        </div>

                        <!-- Price in COP -->
                        <div class="mt-auto d-flex flex-wrap align-items-baseline gap-2">
                            <span class="fs-5 fw-bold text-dark">{{ format_cop($product->price) }}</span>
                            @if($product->compare_price && $product->compare_price > $product->price)
                                <span class="text-muted text-decoration-line-through small">{{ format_cop($product->compare_price) }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>

<!-- Promo Promotional Banner -->
<section class="container py-5">
    <div class="bg-primary text-white rounded-4 p-4 p-md-5 position-relative overflow-hidden shadow-lg" style="background: linear-gradient(135deg, #1e1b4b 0%, #4338ca 100%) !important;">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold mb-3">OFERTA LIMITADA COLOMBIA</span>
                <h2 class="display-6 fw-bold mb-3">Aprovecha hasta un 20% de Descuento Extra</h2>
                <p class="lead mb-4 text-light text-opacity-90">
                    Usa el cupón <strong class="bg-white text-dark px-2 py-1 rounded font-monospace">PROMO20</strong> al finalizar tu compra para pedidos mayores a <strong>$ 200.000 COP</strong>.
                </p>
                <a href="{{ route('shop.index', ['on_sale' => 1]) }}" class="btn btn-warning rounded-pill px-4 py-2 fw-bold text-dark">
                    <i class="bi bi-tag-fill me-1"></i> Comprar con Descuento
                </a>
            </div>
            <div class="col-lg-4 d-none d-lg-block text-end">
                <i class="bi bi-gift-fill text-white opacity-25" style="font-size: 10rem;"></i>
            </div>
        </div>
    </div>
</section>

<!-- New Arrivals & Best Deals Section -->
<section class="container py-4">
    <div class="row g-4">
        <!-- New Arrivals -->
        <div class="col-lg-6">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-sparkles text-warning me-2"></i>Novedades</h4>
                <a href="{{ route('shop.index') }}" class="text-primary small fw-semibold text-decoration-none">Ver más &rarr;</a>
            </div>
            <div class="row g-3">
                @foreach($newArrivals->take(4) as $product)
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-3 p-2 bg-white">
                            <div class="d-flex align-items-center gap-3">
                                <a href="{{ route('shop.show', $product->slug) }}">
                                    <img src="{{ $product->image }}" alt="{{ $product->name }}" class="rounded-3 object-fit-cover" style="width: 80px; height: 80px;">
                                </a>
                                <div class="flex-grow-1">
                                    <span class="badge bg-light text-muted small">{{ $product->category ? $product->category->name : 'General' }}</span>
                                    <h6 class="mb-1">
                                        <a href="{{ route('shop.show', $product->slug) }}" class="text-dark text-decoration-none fw-semibold">
                                            {{ Str::limit($product->name, 35) }}
                                        </a>
                                    </h6>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <strong class="text-primary">{{ format_cop($product->price) }}</strong>
                                            @if($product->compare_price)
                                                <small class="text-muted text-decoration-line-through">{{ format_cop($product->compare_price) }}</small>
                                            @endif
                                        </div>
                                        <form action="{{ route('cart.add') }}" method="POST" class="ajax-add-to-cart">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <button type="submit" class="btn btn-sm btn-outline-primary rounded-circle" style="width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center;" title="Agregar">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Best Sellers -->
        <div class="col-lg-6">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-fire text-danger me-2"></i>Los Más Vendidos</h4>
                <a href="{{ route('shop.index', ['sort' => 'popular']) }}" class="text-primary small fw-semibold text-decoration-none">Ver más &rarr;</a>
            </div>
            <div class="row g-3">
                @foreach($bestSellers as $product)
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-3 p-2 bg-white">
                            <div class="d-flex align-items-center gap-3">
                                <a href="{{ route('shop.show', $product->slug) }}">
                                    <img src="{{ $product->image }}" alt="{{ $product->name }}" class="rounded-3 object-fit-cover" style="width: 80px; height: 80px;">
                                </a>
                                <div class="flex-grow-1">
                                    <span class="badge bg-light text-muted small">{{ $product->category ? $product->category->name : 'General' }}</span>
                                    <h6 class="mb-1">
                                        <a href="{{ route('shop.show', $product->slug) }}" class="text-dark text-decoration-none fw-semibold">
                                            {{ Str::limit($product->name, 35) }}
                                        </a>
                                    </h6>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <strong class="text-primary">{{ format_cop($product->price) }}</strong>
                                            @if($product->compare_price)
                                                <small class="text-muted text-decoration-line-through">{{ format_cop($product->compare_price) }}</small>
                                            @endif
                                        </div>
                                        <form action="{{ route('cart.add') }}" method="POST" class="ajax-add-to-cart">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <button type="submit" class="btn btn-sm btn-outline-primary rounded-circle" style="width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center;" title="Agregar">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<!-- Features Value Proposition Grid -->
<section class="container py-5">
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-md-5">
        <div class="row g-4 text-center">
            <div class="col-md-3 col-6">
                <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex p-3 mb-3">
                    <i class="bi bi-box-seam fs-3"></i>
                </div>
                <h6 class="fw-bold mb-1">Envíos a Toda Colombia</h6>
                <p class="small text-muted mb-0">Entregas en 24 a 48 horas en ciudades principales.</p>
            </div>
            <div class="col-md-3 col-6">
                <div class="bg-success-subtle text-success rounded-circle d-inline-flex p-3 mb-3">
                    <i class="bi bi-shield-lock fs-3"></i>
                </div>
                <h6 class="fw-bold mb-1">Pagos con PSE & Nequi</h6>
                <p class="small text-muted mb-0">Encriptación SSL y transacciones 100% seguras.</p>
            </div>
            <div class="col-md-3 col-6">
                <div class="bg-warning-subtle text-warning rounded-circle d-inline-flex p-3 mb-3">
                    <i class="bi bi-arrow-repeat fs-3"></i>
                </div>
                <h6 class="fw-bold mb-1">Garantía Nacional</h6>
                <p class="small text-muted mb-0">Devoluciones fáciles durante los primeros 30 días.</p>
            </div>
            <div class="col-md-3 col-6">
                <div class="bg-info-subtle text-info rounded-circle d-inline-flex p-3 mb-3">
                    <i class="bi bi-headset fs-3"></i>
                </div>
                <h6 class="fw-bold mb-1">Soporte Local 24/7</h6>
                <p class="small text-muted mb-0">Atención personalizada por WhatsApp y teléfono.</p>
            </div>
        </div>
    </div>
</section>
@endsection
