<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'NovaStore') - Tu Tienda Virtual Premium</title>
    <meta name="description" content="@yield('meta_description', 'Descubre los mejores productos en tecnología, moda, calzado y hogar con envíos rápidos y pagos 100% seguros.')">

    <!-- Vite Assets -->
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
    @php
        $cartService = app(\App\Services\CartService::class);
        $cartCount = $cartService->count();
        $cartItems = $cartService->getItems();
        $cartSubtotal = $cartService->getSubtotal();
    @endphp

    <!-- Top Bar -->
    <div class="top-bar d-none d-md-block">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <span><i class="bi bi-truck text-warning me-1"></i> Envío gratis en compras mayores a <strong>$ 150.000 COP</strong></span>
                <span><i class="bi bi-shield-check text-success me-1"></i> Garantía de satisfacción 30 días</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span><i class="bi bi-headset me-1"></i> Soporte Colombia: +57 310 123 4567</span>
                @auth
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}" class="badge bg-danger text-decoration-none px-2 py-1">
                            <i class="bi bi-speedometer2 me-1"></i> Panel Admin
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <header class="glass-header">
        <nav class="navbar navbar-expand-lg py-3">
            <div class="container">
                <!-- Brand -->
                <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
                    <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-shop-window fs-5"></i>
                    </div>
                    <span class="fs-4 fw-bold brand-gradient">NovaStore</span>
                </a>

                <!-- Mobile Toggle Button -->
                <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Navbar Links & Search -->
                <div class="collapse navbar-collapse" id="mainNavbar">
                    <!-- Nav Items -->
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4 fw-semibold">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('home') ? 'active text-primary' : '' }}" href="{{ route('home') }}">Inicio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('shop.index') && !request()->has('on_sale') ? 'active text-primary' : '' }}" href="{{ route('shop.index') }}">Catálogo</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->has('on_sale') ? 'active text-primary' : '' }}" href="{{ route('shop.index', ['on_sale' => 1]) }}">
                                <span class="badge bg-danger-subtle text-danger me-1">Hot</span> Ofertas
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Categorías
                            </a>
                            <ul class="dropdown-menu shadow-sm border-0 rounded-3">
                                <li><a class="dropdown-item py-2" href="{{ route('shop.index', ['category' => 'tecnologia-gadgets']) }}"><i class="bi bi-laptop me-2 text-primary"></i> Tecnología & Gadgets</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('shop.index', ['category' => 'moda-tendencias']) }}"><i class="bi bi-bag-heart me-2 text-primary"></i> Moda & Ropa</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('shop.index', ['category' => 'calzado-sneakers']) }}"><i class="bi bi-fire me-2 text-primary"></i> Calzado & Sneakers</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('shop.index', ['category' => 'audio-sonido-pro']) }}"><i class="bi bi-headphones me-2 text-primary"></i> Audio & Sonido</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('shop.index', ['category' => 'relojes-accesorios']) }}"><i class="bi bi-smartwatch me-2 text-primary"></i> Relojes & Accesorios</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item py-2 fw-semibold text-primary" href="{{ route('shop.index') }}">Ver todas las categorías &rarr;</a></li>
                            </ul>
                        </li>
                    </ul>

                    <!-- Search Form -->
                    <form action="{{ route('shop.index') }}" method="GET" class="d-flex my-2 my-lg-0 me-lg-4 flex-grow-1" style="max-width: 320px;">
                        <div class="input-group">
                            <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm rounded-start-pill ps-3" placeholder="Buscar productos, marcas..." aria-label="Search">
                            <button class="btn btn-outline-secondary rounded-end-pill px-3 border-start-0" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>

                    <!-- User and Cart Actions -->
                    <div class="d-flex align-items-center gap-2 mt-2 mt-lg-0">
                        <!-- Offcanvas Cart Trigger -->
                        <button type="button" class="btn btn-light rounded-pill position-relative px-3 py-2 border d-flex align-items-center gap-2" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart" aria-controls="offcanvasCart">
                            <i class="bi bi-cart3 fs-5 text-primary"></i>
                            <span class="d-none d-sm-inline fw-semibold small">Carrito</span>
                            <span class="cart-badge-count badge rounded-pill bg-danger {{ $cartCount > 0 ? '' : 'd-none' }}">
                                {{ $cartCount }}
                            </span>
                        </button>

                        @auth
                            <!-- User Dropdown -->
                            <div class="dropdown">
                                <button class="btn btn-outline-dark rounded-pill px-3 py-2 dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-person-circle fs-5 text-primary"></i>
                                    <span class="fw-semibold small">{{ Str::limit(auth()->user()->name, 14) }}</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
                                    <li class="px-3 py-2 border-bottom">
                                        <div class="fw-bold">{{ auth()->user()->name }}</div>
                                        <div class="small text-muted text-truncate">{{ auth()->user()->email }}</div>
                                    </li>
                                    @if(auth()->user()->is_admin)
                                        <li>
                                            <a class="dropdown-item py-2 text-danger fw-semibold" href="{{ route('admin.dashboard') }}">
                                                <i class="bi bi-speedometer2 me-2"></i> Panel de Administración
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                    @endif
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('orders.index') }}">
                                            <i class="bi bi-box-seam me-2 text-primary"></i> Mis Pedidos
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('profile.edit') }}">
                                            <i class="bi bi-person-gear me-2 text-primary"></i> Mi Perfil
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item py-2 text-danger">
                                                <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        @else
                            <!-- Guest Links -->
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                                    Iniciar Sesión
                                </a>
                                <a href="{{ route('register') }}" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold">
                                    Registrarse
                                </a>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Offcanvas Shopping Cart Drawer -->
    <div class="offcanvas offcanvas-end offcanvas-cart" tabindex="-1" id="offcanvasCart" aria-labelledby="offcanvasCartLabel">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold d-flex align-items-center gap-2" id="offcanvasCartLabel">
                <i class="bi bi-bag-check text-primary"></i> Tu Carrito de Compras
            </h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column">
            <!-- Empty state -->
            <div id="offcanvasCartEmpty" class="text-center my-auto py-5 {{ count($cartItems) > 0 ? 'd-none' : '' }}">
                <div class="bg-light rounded-circle d-inline-flex p-4 mb-3 text-muted">
                    <i class="bi bi-cart-x fs-1"></i>
                </div>
                <h6 class="fw-bold mb-2">Tu carrito está vacío</h6>
                <p class="text-muted small mb-4">Explora nuestro catálogo y descubre miles de ofertas exclusivas.</p>
                <a href="{{ route('shop.index') }}" class="btn btn-primary btn-sm rounded-pill px-4">
                    Ir al Catálogo
                </a>
            </div>

            <!-- Items List -->
            <div id="offcanvasCartItems" class="flex-grow-1 overflow-auto pe-1 {{ count($cartItems) === 0 ? 'd-none' : '' }}">
                @foreach($cartItems as $item)
                    <div class="cart-item d-flex gap-3 align-items-center">
                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="rounded-3 object-fit-cover" style="width: 64px; height: 64px;">
                        <div class="flex-grow-1">
                            <h6 class="mb-1 text-truncate" style="max-width: 180px;">{{ $item['name'] }}</h6>
                            <div class="text-muted small">{{ $item['quantity'] }} x <strong class="text-primary">{{ format_cop($item['price']) }}</strong></div>
                        </div>
                        <button type="button" class="btn btn-sm text-danger remove-cart-item-btn" data-id="{{ $item['id'] }}" title="Eliminar">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </div>
                @endforeach
            </div>

            <!-- Footer & Checkout -->
            <div id="offcanvasCartFooter" class="border-top pt-3 mt-auto {{ count($cartItems) === 0 ? 'd-none' : '' }}">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Subtotal:</span>
                    <strong id="offcanvasCartSubtotal" class="fs-5 text-dark">{{ format_cop($cartSubtotal) }}</strong>
                </div>
                <div class="d-grid gap-2">
                    <a href="{{ route('cart.index') }}" class="btn btn-outline-primary rounded-pill fw-semibold">
                        <i class="bi bi-cart3 me-1"></i> Ver Carrito Completo
                    </a>
                    <a href="{{ route('checkout.index') }}" class="btn btn-primary rounded-pill fw-semibold shadow-sm">
                        <i class="bi bi-credit-card me-1"></i> Proceder al Pago
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <div class="container mt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0 d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show rounded-3 shadow-sm border-0 d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-exclamation-circle-fill fs-5 text-warning"></i>
                <div>{{ session('warning') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer (Accessible Light Mode) -->
    <footer class="site-footer mt-5 bg-white border-top">
        <div class="container">
            <div class="row g-4 mb-5">
                <!-- Col 1: Brand Info -->
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px;">
                            <i class="bi bi-shop-window"></i>
                        </div>
                        <span class="fs-4 fw-bold text-dark">NovaStore</span>
                    </div>
                    <p class="small text-muted mb-4">
                        Tu destino preferido para compras online. Productos originales, precios competitivos y entregas rápidas garantizadas en todo el país.
                    </p>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-light border btn-sm rounded-circle text-primary" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="btn btn-light border btn-sm rounded-circle text-primary" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="btn btn-light border btn-sm rounded-circle text-primary" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="btn btn-light border btn-sm rounded-circle text-primary" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;"><i class="bi bi-tiktok"></i></a>
                    </div>
                </div>

                <!-- Col 2: Navigation Links -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="text-dark fw-bold mb-3">Enlaces Rápidos</h6>
                    <ul class="list-unstyled small d-flex flex-column gap-2">
                        <li><a href="{{ route('home') }}" class="text-muted text-decoration-none">Inicio</a></li>
                        <li><a href="{{ route('shop.index') }}" class="text-muted text-decoration-none">Catálogo Completo</a></li>
                        <li><a href="{{ route('shop.index', ['on_sale' => 1]) }}" class="text-muted text-decoration-none">Ofertas Especiales</a></li>
                        <li><a href="{{ route('cart.index') }}" class="text-muted text-decoration-none">Mi Carrito</a></li>
                        <li><a href="{{ route('orders.index') }}" class="text-muted text-decoration-none">Rastreo de Pedidos</a></li>
                    </ul>
                </div>

                <!-- Col 3: Categories -->
                <div class="col-lg-3 col-md-6 col-6">
                    <h6 class="text-dark fw-bold mb-3">Categorías Populares</h6>
                    <ul class="list-unstyled small d-flex flex-column gap-2">
                        <li><a href="{{ route('shop.index', ['category' => 'tecnologia-gadgets']) }}" class="text-muted text-decoration-none">Tecnología & Gadgets</a></li>
                        <li><a href="{{ route('shop.index', ['category' => 'moda-tendencias']) }}" class="text-muted text-decoration-none">Moda & Tendencias</a></li>
                        <li><a href="{{ route('shop.index', ['category' => 'calzado-sneakers']) }}" class="text-muted text-decoration-none">Calzado & Sneakers</a></li>
                        <li><a href="{{ route('shop.index', ['category' => 'audio-sonido-pro']) }}" class="text-muted text-decoration-none">Audio & Sonido</a></li>
                        <li><a href="{{ route('shop.index', ['category' => 'relojes-accesorios']) }}" class="text-muted text-decoration-none">Relojes & Accesorios</a></li>
                    </ul>
                </div>

                <!-- Col 4: Newsletter -->
                <div class="col-lg-3 col-md-6">
                    <h6 class="text-dark fw-bold mb-3">Suscríbete a Novedades</h6>
                    <p class="small text-muted mb-3">Recibe un 15% de descuento en tu primera compra y ofertas exclusivas.</p>
                    <form onsubmit="event.preventDefault(); window.showToast('¡Gracias por suscribirte al boletín!', 'success');" class="mb-3">
                        <div class="input-group">
                            <input type="email" class="form-control form-control-sm bg-light text-dark border" placeholder="tu@email.com" required>
                            <button class="btn btn-primary btn-sm px-3" type="submit"><i class="bi bi-send-fill"></i></button>
                        </div>
                    </form>
                    <div class="small text-muted">
                        <i class="bi bi-shield-check text-success me-1"></i> Tus datos están 100% protegidos.
                    </div>
                </div>
            </div>

            <!-- Bottom: Payment & Copyright -->
            <div class="row pt-4 border-top align-items-center">
                <div class="col-md-6 text-center text-md-start small text-muted mb-3 mb-md-0">
                    &copy; {{ date('Y') }} <strong>NovaStore</strong>. Todos los derechos reservados.
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <div class="d-inline-flex gap-2 text-secondary fs-4">
                        <i class="bi bi-credit-card text-primary" title="Tarjetas de Crédito / Débito"></i>
                        <i class="bi bi-paypal text-primary" title="PayPal"></i>
                        <i class="bi bi-shield-check text-success" title="Compra Protegida SSL"></i>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
