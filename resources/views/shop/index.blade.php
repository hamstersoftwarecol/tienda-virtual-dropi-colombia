@extends('layouts.app')

@section('title', 'Catálogo de Productos')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Catálogo</li>
            @if(request('category'))
                @php
                    $activeCat = $categories->firstWhere('slug', request('category'));
                @endphp
                @if($activeCat)
                    <li class="breadcrumb-item active text-primary fw-bold" aria-current="page">{{ $activeCat->name }}</li>
                @endif
            @endif
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Filters Sidebar -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 90px; z-index: 10;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-funnel text-primary me-2"></i>Filtros</h5>
                    @if(request()->hasAny(['q', 'category', 'min_price', 'max_price', 'in_stock', 'on_sale', 'sort']))
                        <a href="{{ route('shop.index') }}" class="text-danger small fw-semibold text-decoration-none">
                            <i class="bi bi-x-circle me-1"></i> Limpiar
                        </a>
                    @endif
                </div>

                <form action="{{ route('shop.index') }}" method="GET" id="filterForm">
                    <!-- Search query preserve -->
                    @if(request('q'))
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Búsqueda actual:</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="q" value="{{ request('q') }}" class="form-control rounded-start-pill">
                                <button class="btn btn-outline-secondary rounded-end-pill" type="submit"><i class="bi bi-search"></i></button>
                            </div>
                        </div>
                    @endif

                    <!-- Categories -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase text-muted letter-spacing-1 mb-2">Categorías</label>
                        <div class="d-flex flex-column gap-1">
                            <a href="{{ route('shop.index', request()->except('category', 'page')) }}" class="p-2 rounded-3 text-decoration-none d-flex justify-content-between align-items-center {{ !request('category') ? 'bg-primary-subtle text-primary fw-bold' : 'text-dark hover-bg-light' }}">
                                <span>Todas</span>
                                <span class="badge bg-light text-dark rounded-pill">{{ $categories->sum('products_count') }}</span>
                            </a>
                            @foreach($categories as $category)
                                <a href="{{ route('shop.index', array_merge(request()->except('page'), ['category' => $category->slug])) }}" class="p-2 rounded-3 text-decoration-none d-flex justify-content-between align-items-center {{ request('category') === $category->slug ? 'bg-primary-subtle text-primary fw-bold' : 'text-dark hover-bg-light' }}">
                                    <span><i class="bi {{ $category->icon ?: 'bi-tag' }} me-2 text-muted"></i> {{ $category->name }}</span>
                                    <span class="badge bg-light text-dark rounded-pill">{{ $category->products_count }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- Price Range in COP -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase text-muted letter-spacing-1 mb-2">Precio (COP)</label>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="min_price" value="{{ request('min_price') }}" class="form-control" placeholder="Mín (COP)" min="0" step="5000">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="max_price" value="{{ request('max_price') }}" class="form-control" placeholder="Máx (COP)" min="0" step="5000">
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- Special Filters -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase text-muted letter-spacing-1 mb-2">Disponibilidad & Ofertas</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="in_stock" value="1" id="inStockCheck" {{ request('in_stock') == '1' ? 'checked' : '' }} onchange="this.form.submit()">
                            <label class="form-check-label small" for="inStockCheck">
                                En Stock únicamente
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="on_sale" value="1" id="onSaleCheck" {{ request('on_sale') == '1' ? 'checked' : '' }} onchange="this.form.submit()">
                            <label class="form-check-label small text-danger fw-semibold" for="onSaleCheck">
                                <i class="bi bi-tag-fill me-1"></i> En Oferta / Descuento
                            </label>
                        </div>
                    </div>

                    @if(request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                    @if(request('sort'))
                        <input type="hidden" name="sort" value="{{ request('sort') }}">
                    @endif

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill">
                            Aplicar Filtros
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Products List Area -->
        <div class="col-lg-9">
            <!-- Header Bar -->
            <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">
                            @if(request('q'))
                                Resultados para: <span class="text-primary">"{{ request('q') }}"</span>
                            @elseif(request('category'))
                                {{ $categories->firstWhere('slug', request('category'))->name ?? 'Catálogo' }}
                            @elseif(request('on_sale'))
                                Ofertas Especiales (COP)
                            @else
                                Todos los Productos
                            @endif
                        </h4>
                        <small class="text-muted">Mostrando {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} de {{ $products->total() }} productos</small>
                    </div>

                    <!-- Sort Form -->
                    <div class="d-flex align-items-center gap-2">
                        <label class="small text-muted text-nowrap">Ordenar por:</label>
                        <select class="form-select form-select-sm rounded-pill shadow-none" style="width: auto;" onchange="window.location.href = this.value">
                            <option value="{{ route('shop.index', array_merge(request()->except('sort', 'page'), ['sort' => 'latest'])) }}" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>Más Recientes</option>
                            <option value="{{ route('shop.index', array_merge(request()->except('sort', 'page'), ['sort' => 'popular'])) }}" {{ request('sort') === 'popular' ? 'selected' : '' }}>Más Vendidos</option>
                            <option value="{{ route('shop.index', array_merge(request()->except('sort', 'page'), ['sort' => 'rating'])) }}" {{ request('sort') === 'rating' ? 'selected' : '' }}>Mejor Valorados</option>
                            <option value="{{ route('shop.index', array_merge(request()->except('sort', 'page'), ['sort' => 'price_asc'])) }}" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Precio: Menor a Mayor</option>
                            <option value="{{ route('shop.index', array_merge(request()->except('sort', 'page'), ['sort' => 'price_desc'])) }}" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Precio: Mayor a Menor</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            @if($products->isEmpty())
                <div class="card border-0 shadow-sm rounded-4 text-center py-5 bg-white">
                    <div class="py-4">
                        <i class="bi bi-search fs-1 text-muted mb-3 d-block"></i>
                        <h5 class="fw-bold mb-2">No se encontraron productos</h5>
                        <p class="text-muted small mb-4">Intenta ajustar los filtros de búsqueda o categoría.</p>
                        <a href="{{ route('shop.index') }}" class="btn btn-primary rounded-pill px-4">
                            Ver Todos los Productos
                        </a>
                    </div>
                </div>
            @else
                <div class="row g-4 mb-4">
                    @foreach($products as $product)
                        <div class="col-6 col-md-4">
                            <div class="card product-card">
                                <div class="product-img-wrapper">
                                    <a href="{{ route('shop.show', $product->slug) }}">
                                        <img src="{{ $product->image }}" alt="{{ $product->name }}" loading="lazy">
                                    </a>
                                    @if($product->badge)
                                        <span class="product-badge bg-dark text-white">{{ $product->badge }}</span>
                                    @elseif($product->discount_percent > 0)
                                        <span class="product-badge badge-discount">-{{ $product->discount_percent }}%</span>
                                    @endif

                                    <!-- Quick Actions -->
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

                                    <!-- Price & Stock in COP -->
                                    <div class="mt-auto d-flex flex-wrap align-items-baseline justify-content-between gap-1">
                                        <div class="d-flex flex-wrap align-items-baseline gap-2">
                                            <span class="fs-5 fw-bold text-dark">{{ format_cop($product->price) }}</span>
                                            @if($product->compare_price && $product->compare_price > $product->price)
                                                <span class="text-muted text-decoration-line-through small">{{ format_cop($product->compare_price) }}</span>
                                            @endif
                                        </div>
                                        @if($product->stock <= 0)
                                            <span class="badge bg-danger-subtle text-danger small">Agotado</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
