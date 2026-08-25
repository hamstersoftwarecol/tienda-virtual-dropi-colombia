@props(['product'])

@php
    $savings = ($product->compare_price && $product->compare_price > $product->price) ? ($product->compare_price - $product->price) : 0;
@endphp

<div class="card product-card h-100 border-0 shadow-sm rounded-4 bg-white overflow-hidden position-relative" style="transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
    <!-- Top Badges Overlay -->
    <div class="position-absolute top-0 start-0 m-2 d-flex flex-column gap-1" style="z-index: 3;">
        @if($product->badge)
            <span class="badge bg-dark text-white rounded-pill px-2.5 py-1 small fw-bold shadow-sm">
                {{ $product->badge }}
            </span>
        @elseif($product->discount_percent > 0)
            <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 small fw-bold shadow-sm">
                -{{ $product->discount_percent }}% OFF
            </span>
        @endif

        @if($product->is_dropi_product || $product->dropi_id)
            <span class="badge bg-success text-white rounded-pill px-2 py-0.5 small shadow-sm" style="font-size: 0.65rem;">
                <i class="bi bi-truck me-0.5"></i> Contra Entrega
            </span>
        @endif
    </div>

    <!-- Product Image Box -->
    <div class="product-img-wrapper position-relative overflow-hidden bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
        <a href="{{ route('shop.show', $product->slug) }}" class="w-100 h-100 d-flex align-items-center justify-content-center p-3">
            <img src="{{ $product->image }}" alt="{{ $product->name }}" loading="lazy" class="img-fluid object-fit-contain w-100 h-100" style="transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);">
        </a>

        <!-- Quick Action Overlay on Hover -->
        <div class="quick-actions position-absolute bottom-0 start-0 end-0 p-2 d-none d-md-block" style="z-index: 4;">
            <form action="{{ route('cart.add') }}" method="POST" class="ajax-add-to-cart">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 shadow fw-bold btn-sm d-flex align-items-center justify-content-center gap-1.5" {{ $product->stock <= 0 ? 'disabled' : '' }}>
                    <i class="bi bi-cart-plus-fill"></i> {{ $product->stock <= 0 ? 'Agotado' : 'Agregar al Carrito' }}
                </button>
            </form>
        </div>
    </div>

    <!-- Card Body -->
    <div class="card-body p-3 d-flex flex-column">
        <!-- Category & Stock Status -->
        <div class="d-flex align-items-center justify-content-between mb-1">
            <span class="text-muted text-uppercase fw-semibold text-truncate" style="font-size: 0.7rem; max-width: 60%;">
                {{ $product->category ? $product->category->name : 'General' }}
            </span>

            @if($product->stock <= 0)
                <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">Agotado</span>
            @elseif($product->stock <= 5)
                <span class="badge bg-warning-subtle text-warning border border-warning border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">🔥 Últimas {{ $product->stock }}</span>
            @else
                <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">En Stock</span>
            @endif
        </div>

        <!-- Product Name -->
        <h6 class="fw-bold mb-1 text-truncate-2" style="font-size: 0.92rem; min-height: 2.4rem;">
            <a href="{{ route('shop.show', $product->slug) }}" class="text-dark text-decoration-none" title="{{ $product->name }}">
                {{ $product->name }}
            </a>
        </h6>

        <!-- Ratings & Reviews -->
        <div class="d-flex align-items-center gap-1 mb-2">
            <div class="rating-stars small">
                <i class="bi bi-star-fill text-warning"></i>
                <span class="fw-bold text-dark ms-1">{{ number_format($product->rating, 1) }}</span>
            </div>
            <span class="text-muted" style="font-size: 0.75rem;">({{ $product->reviews_count }})</span>
        </div>

        <!-- Price in COP -->
        <div class="mt-auto pt-2 border-top">
            <div class="d-flex flex-wrap align-items-baseline gap-1.5">
                <span class="fs-5 fw-extrabold text-primary font-monospace">{{ format_cop($product->price) }}</span>
                @if($product->compare_price && $product->compare_price > $product->price)
                    <span class="text-muted text-decoration-line-through small" style="font-size: 0.78rem;">{{ format_cop($product->compare_price) }}</span>
                @endif
            </div>

            @if($savings > 0)
                <div class="text-success small fw-semibold mt-0.5" style="font-size: 0.72rem;">
                    <i class="bi bi-arrow-down-circle-fill me-0.5"></i> Ahorras {{ format_cop($savings) }}
                </div>
            @endif

            <!-- Mobile Add to Cart Button -->
            <div class="d-md-none mt-2">
                <form action="{{ route('cart.add') }}" method="POST" class="ajax-add-to-cart">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="btn btn-sm btn-primary w-100 rounded-pill py-1.5 fw-bold shadow-sm" {{ $product->stock <= 0 ? 'disabled' : '' }}>
                        <i class="bi bi-cart-plus-fill me-1"></i> {{ $product->stock <= 0 ? 'Agotado' : 'Agregar' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
