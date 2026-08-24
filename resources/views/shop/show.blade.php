@extends('layouts.app')

@section('title', $product->name)
@section('meta_description', Str::limit($product->short_description ?? $product->description, 150))

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('shop.index') }}" class="text-decoration-none text-muted">Catálogo</a></li>
            @if($product->category)
                <li class="breadcrumb-item"><a href="{{ route('shop.index', ['category' => $product->category->slug]) }}" class="text-decoration-none text-muted">{{ $product->category->name }}</a></li>
            @endif
            <li class="breadcrumb-item active text-truncate" style="max-width: 250px;" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    <!-- Main Product Details Card -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-md-5 mb-5">
        <div class="row g-4 g-lg-5">
            <!-- Left: Image Gallery -->
            <div class="col-lg-6">
                <div class="position-relative mb-3 bg-light rounded-4 overflow-hidden text-center" style="max-height: 480px;">
                    <img id="mainProductImage" src="{{ $product->image }}" alt="{{ $product->name }}" class="img-fluid rounded-4 object-fit-contain w-100" style="max-height: 480px; transition: all 0.3s ease;">
                    @if($product->badge)
                        <span class="position-absolute top-0 start-0 m-3 badge bg-dark px-3 py-2 rounded-pill fs-6">{{ $product->badge }}</span>
                    @elseif($product->discount_percent > 0)
                        <span class="position-absolute top-0 start-0 m-3 badge badge-discount px-3 py-2 rounded-pill fs-6">-{{ $product->discount_percent }}% OFF</span>
                    @endif
                </div>

                <!-- Thumbnails Gallery if multiple images exist -->
                @if($product->images && count($product->images) > 1)
                    <div class="d-flex gap-2 overflow-auto py-2">
                        @foreach($product->images as $index => $img)
                            <button type="button" class="btn p-0 border rounded-3 overflow-hidden {{ $loop->first ? 'border-primary border-2' : '' }}" onclick="document.getElementById('mainProductImage').src = '{{ $img }}'; document.querySelectorAll('.gallery-thumb').forEach(b => b.classList.remove('border-primary', 'border-2')); this.classList.add('border-primary', 'border-2');" style="width: 76px; height: 76px;">
                                <img src="{{ $img }}" class="gallery-thumb w-100 h-100 object-fit-cover" alt="Thumbnail {{ $index }}">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Right: Product Info & Form -->
            <div class="col-lg-6 d-flex flex-column">
                <div class="mb-2">
                    <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase px-3 py-1 rounded-pill">
                        {{ $product->category ? $product->category->name : 'General' }}
                    </span>
                    @if($product->sku)
                        <span class="text-muted small ms-2">SKU: <strong>{{ $product->sku }}</strong></span>
                    @endif
                </div>

                <h1 class="fw-bold text-dark mb-2 display-6">{{ $product->name }}</h1>

                <!-- Ratings & Reviews -->
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="rating-stars">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="bi {{ $i <= round($product->rating) ? 'bi-star-fill' : 'bi-star' }} text-warning"></i>
                        @endfor
                        <span class="fw-bold text-dark ms-1">{{ number_format($product->rating, 1) }}</span>
                    </div>
                    <span class="text-muted">|</span>
                    <a href="#reviews-section" class="text-muted small text-decoration-none">
                        {{ $product->reviews_count }} valoraciones de clientes
                    </a>
                </div>

                <!-- Pricing in COP -->
                <div class="d-flex flex-wrap align-items-baseline gap-3 mb-4 p-3 bg-light rounded-4">
                    <span class="display-6 fw-bold text-primary">{{ format_cop($product->price) }}</span>
                    @if($product->compare_price && $product->compare_price > $product->price)
                        <span class="fs-5 text-muted text-decoration-line-through">{{ format_cop($product->compare_price) }}</span>
                        <span class="badge bg-danger rounded-pill px-2 py-1 small">
                            Ahorras {{ format_cop($product->compare_price - $product->price) }}
                        </span>
                    @endif
                </div>

                <!-- Stock Status -->
                <div class="mb-3">
                    @if($product->stock > 0)
                        <span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">
                            <i class="bi bi-check-circle-fill me-1"></i> En Stock ({{ $product->stock }} unidades disponibles)
                        </span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill">
                            <i class="bi bi-x-circle-fill me-1"></i> Agotado temporalmente
                        </span>
                    @endif
                </div>

                <!-- Short description -->
                <p class="text-muted mb-4">
                    {{ $product->short_description ?? Str::limit($product->description, 180) }}
                </p>

                <!-- Add to Cart Form -->
                @if($product->stock > 0)
                    <form action="{{ route('cart.add') }}" method="POST" class="ajax-add-to-cart mt-auto">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                        <div class="d-flex flex-wrap gap-3 align-items-center mb-4">
                            <!-- Quantity -->
                            <div>
                                <label class="form-label small fw-bold text-muted mb-1 d-block">Cantidad:</label>
                                <div class="quantity-control">
                                    <button type="button" onclick="const input = this.parentNode.querySelector('input[type=number]'); input.stepDown();">-</button>
                                    <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}" readonly>
                                    <button type="button" onclick="const input = this.parentNode.querySelector('input[type=number]'); input.stepUp();">+</button>
                                </div>
                            </div>

                            <!-- CTA Buttons -->
                            <div class="d-flex gap-2 flex-grow-1 align-self-end">
                                <button type="submit" class="btn btn-primary rounded-pill flex-grow-1 py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-cart-plus-fill fs-5"></i> Añadir al Carrito
                                </button>
                            </div>
                        </div>
                    </form>
                @else
                    <button class="btn btn-secondary rounded-pill py-3 w-100 disabled mb-4" disabled>
                        Producto Agotado
                    </button>
                @endif

                <!-- Benefits info bar -->
                <div class="row g-2 pt-3 border-top text-muted small">
                    <div class="col-6 d-flex align-items-center gap-2">
                        <i class="bi bi-truck text-primary fs-5"></i>
                        <span>Envío gratis desde $ 150.000 COP</span>
                    </div>
                    <div class="col-6 d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-success fs-5"></i>
                        <span>Compra 100% Protegida</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Tabs: Description, Specs, Reviews -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-md-5 mb-5" id="reviews-section">
        <ul class="nav nav-pills mb-4 gap-2 border-bottom pb-3" id="productTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill px-4 fw-semibold" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc-tab-pane" type="button" role="tab">
                    <i class="bi bi-text-paragraph me-2"></i> Descripción
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 fw-semibold" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews-tab-pane" type="button" role="tab">
                    <i class="bi bi-chat-heart me-2"></i> Reseñas ({{ $product->reviews_count }})
                </button>
            </li>
        </ul>

        <div class="tab-content" id="productTabContent">
            <!-- Description -->
            <div class="tab-pane fade show active" id="desc-tab-pane" role="tabpanel">
                <h5 class="fw-bold mb-3">Detalles del Producto</h5>
                <div class="text-secondary lh-lg">
                    {!! nl2br(e($product->description)) !!}
                </div>
            </div>

            <!-- Reviews -->
            <div class="tab-pane fade" id="reviews-tab-pane" role="tabpanel">
                <div class="row g-4">
                    <!-- Reviews list -->
                    <div class="col-lg-7">
                        <h5 class="fw-bold mb-3">Opiniones de Clientes</h5>
                        @if($product->reviews->isEmpty())
                            <div class="p-4 bg-light rounded-4 text-center text-muted">
                                <i class="bi bi-chat-square-dots fs-2 mb-2 d-block"></i>
                                Sé el primero en dejar una opinión sobre este producto.
                            </div>
                        @else
                            <div class="d-flex flex-column gap-3">
                                @foreach($product->reviews as $review)
                                    <div class="p-3 bg-light rounded-4 border">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px;">
                                                    {{ substr($review->user->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold">{{ $review->user->name }}</h6>
                                                    <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                                                </div>
                                            </div>
                                            <div class="rating-stars">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="bi {{ $i <= $review->rating ? 'bi-star-fill' : 'bi-star' }} text-warning"></i>
                                                @endfor
                                            </div>
                                        </div>
                                        <p class="mb-0 text-secondary small">{{ $review->comment }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Add Review Form -->
                    <div class="col-lg-5">
                        <div class="p-4 bg-light rounded-4 border">
                            <h5 class="fw-bold mb-3">Escribir una Valoración</h5>
                            @auth
                                <form action="{{ route('reviews.store', $product->id) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Calificación</label>
                                        <select name="rating" class="form-select rounded-3" required>
                                            <option value="5">⭐⭐⭐⭐⭐ (5 Estrellas - Excelente)</option>
                                            <option value="4">⭐⭐⭐⭐ (4 Estrellas - Muy Bueno)</option>
                                            <option value="3">⭐⭐⭐ (3 Estrellas - Bueno)</option>
                                            <option value="2">⭐⭐ (2 Estrellas - Regular)</option>
                                            <option value="1">⭐ (1 Estrella - Malo)</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Tu Comentario</label>
                                        <textarea name="comment" rows="4" class="form-control rounded-3" placeholder="¿Qué te pareció el producto? ¿Cumplió con tus expectativas?" required minlength="5"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary rounded-pill w-100 py-2">
                                        Publicar Valoración
                                    </button>
                                </form>
                            @else
                                <div class="text-center py-4">
                                    <p class="text-muted small mb-3">Debes iniciar sesión para publicar una valoración.</p>
                                    <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm rounded-pill px-4">
                                        Iniciar Sesión
                                    </a>
                                </div>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->isNotEmpty())
        <div class="py-4">
            <h3 class="fw-bold mb-4 text-dark">Productos Relacionados</h3>
            <div class="row g-4">
                @foreach($relatedProducts as $related)
                    <div class="col-6 col-md-3">
                        <div class="card product-card">
                            <div class="product-img-wrapper">
                                <a href="{{ route('shop.show', $related->slug) }}">
                                    <img src="{{ $related->image }}" alt="{{ $related->name }}" loading="lazy">
                                </a>
                                @if($related->badge)
                                    <span class="product-badge bg-dark text-white">{{ $related->badge }}</span>
                                @elseif($related->discount_percent > 0)
                                    <span class="product-badge badge-discount">-{{ $related->discount_percent }}%</span>
                                @endif
                                <div class="quick-actions">
                                    <form action="{{ route('cart.add') }}" method="POST" class="ajax-add-to-cart">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $related->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 shadow-sm fw-semibold">
                                            <i class="bi bi-cart-plus-fill"></i> Agregar
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="card-body p-3 d-flex flex-column">
                                <h6 class="fw-bold mb-2">
                                    <a href="{{ route('shop.show', $related->slug) }}" class="text-dark text-decoration-none text-truncate d-block">
                                        {{ $related->name }}
                                    </a>
                                </h6>
                                <div class="mt-auto d-flex align-items-baseline gap-2">
                                    <span class="fs-5 fw-bold text-dark">{{ format_cop($related->price) }}</span>
                                    @if($related->compare_price)
                                        <span class="text-muted text-decoration-line-through small">{{ format_cop($related->compare_price) }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
