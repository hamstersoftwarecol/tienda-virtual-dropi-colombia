<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Panel') - NovaStore Admin</title>

    <!-- Vite Assets -->
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-light">
    <div class="d-flex min-vh-100">
        <!-- Sidebar -->
        <aside class="admin-sidebar p-3 d-none d-lg-flex flex-column bg-white border-end">
            <!-- Admin Brand -->
            <div class="d-flex align-items-center gap-2 pb-3 mb-3 border-bottom px-2">
                <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px;">
                    <i class="bi bi-shield-lock-fill fs-5"></i>
                </div>
                <div>
                    <h6 class="text-dark fw-bold mb-0">NovaStore</h6>
                    <small class="text-muted" style="font-size: 0.75rem;">Panel de Administración</small>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="nav flex-column mb-auto">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>

                <div class="text-muted small text-uppercase fw-bold px-3 mt-3 mb-1" style="font-size: 0.68rem; letter-spacing: 0.5px;">Catálogo de Productos</div>
                <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">
                    <i class="bi bi-box-seam"></i> Productos en Tienda
                </a>
                <a class="nav-link {{ request()->routeIs('admin.dropi.products*') ? 'active' : '' }}" href="{{ route('admin.dropi.products.index') }}">
                    <i class="bi bi-cloud-arrow-down text-primary"></i> Importar Productos Dropi
                </a>
                <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">
                    <i class="bi bi-tags"></i> Categorías
                </a>

                <div class="text-muted small text-uppercase fw-bold px-3 mt-3 mb-1" style="font-size: 0.68rem; letter-spacing: 0.5px;">Ventas & Pedidos</div>
                <a class="nav-link {{ request()->routeIs('admin.orders.index', 'admin.orders.show') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">
                    <i class="bi bi-receipt"></i> Pedidos
                </a>
                <a class="nav-link {{ request()->routeIs('admin.orders.create') ? 'active' : '' }}" href="{{ route('admin.orders.create') }}">
                    <i class="bi bi-cart-plus text-info"></i> Generar Pedido
                </a>
                <a class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}" href="{{ route('admin.customers.index') }}">
                    <i class="bi bi-people"></i> Clientes & Compradores
                </a>

                <div class="text-muted small text-uppercase fw-bold px-3 mt-3 mb-1" style="font-size: 0.68rem; letter-spacing: 0.5px;">Ajustes & Integraciones</div>
                <a class="nav-link {{ request()->routeIs('admin.dropi.settings*') ? 'active' : '' }}" href="{{ route('admin.dropi.settings') }}">
                    <i class="bi bi-gear-wide-connected text-warning"></i> Configuración Dropi
                </a>
                <a class="nav-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}" href="{{ route('admin.coupons.index') }}">
                    <i class="bi bi-ticket-perforated"></i> Cupones de Descuento
                </a>
                
                <hr class="my-3 text-muted opacity-25">

                <a class="nav-link text-primary fw-semibold" href="{{ route('home') }}" target="_blank">
                    <i class="bi bi-box-arrow-up-right"></i> Ver Tienda Pública
                </a>
            </nav>

            <!-- User Info Footer -->
            <div class="pt-3 border-top px-2 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-person-fill fs-5"></i>
                    </div>
                    <div class="small">
                        <div class="text-dark fw-semibold text-truncate" style="max-width: 120px;">{{ auth()->user()->name }}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">Administrador</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger border-0 p-1" title="Cerrar Sesión">
                        <i class="bi bi-box-arrow-right fs-5"></i>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Wrapper -->
        <div class="admin-main-wrapper flex-grow-1 d-flex flex-column bg-light">
            <!-- Admin Topbar -->
            <header class="bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center shadow-sm">
                <!-- Mobile Toggle -->
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-light d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminMobileSidebar">
                        <i class="bi bi-list fs-5"></i>
                    </button>
                    <h5 class="mb-0 fw-bold text-dark">@yield('page_header', 'Dashboard')</h5>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('home') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                        <i class="bi bi-shop me-1"></i> Ir a la Tienda
                    </a>

                    <!-- Profile Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-light rounded-pill border dropdown-toggle d-flex align-items-center gap-2 py-1 px-3" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle text-primary"></i>
                            <span class="small fw-semibold text-dark">{{ auth()->user()->name }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                            <li><a class="dropdown-item py-2" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i> Mi Perfil</a></li>
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
                </div>
            </header>

            <!-- Admin Mobile Offcanvas -->
            <div class="offcanvas offcanvas-start bg-white text-dark" tabindex="-1" id="adminMobileSidebar">
                <div class="offcanvas-header border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <h5 class="offcanvas-title text-dark fw-bold mb-0">NovaStore Admin</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                </div>
                <div class="offcanvas-body p-3">
                    <nav class="nav flex-column">
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">
                            <i class="bi bi-box-seam"></i> Productos
                        </a>
                        <a class="nav-link {{ request()->routeIs('admin.dropi.products*') ? 'active' : '' }}" href="{{ route('admin.dropi.products.index') }}">
                            <i class="bi bi-cloud-arrow-down text-primary"></i> Importar Dropi
                        </a>
                        <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">
                            <i class="bi bi-receipt"></i> Pedidos
                        </a>
                        <a class="nav-link {{ request()->routeIs('admin.orders.create') ? 'active' : '' }}" href="{{ route('admin.orders.create') }}">
                            <i class="bi bi-cart-plus"></i> Generar Pedido
                        </a>
                        <a class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}" href="{{ route('admin.customers.index') }}">
                            <i class="bi bi-people"></i> Clientes
                        </a>
                        <a class="nav-link {{ request()->routeIs('admin.dropi.settings*') ? 'active' : '' }}" href="{{ route('admin.dropi.settings') }}">
                            <i class="bi bi-gear-wide-connected text-warning"></i> Configuración Dropi
                        </a>
                        <a class="nav-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}" href="{{ route('admin.coupons.index') }}">
                            <i class="bi bi-ticket-perforated"></i> Cupones
                        </a>
                        <hr class="my-3 text-muted opacity-25">
                        <a class="nav-link text-primary fw-semibold" href="{{ route('home') }}" target="_blank">
                            <i class="bi bi-box-arrow-up-right"></i> Ir a la Tienda
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Page Content -->
            <main class="p-4">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 d-flex align-items-center gap-2" role="alert">
                        <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                        <div>{{ session('success') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show rounded-3 shadow-sm border-0 d-flex align-items-center gap-2" role="alert">
                        <i class="bi bi-exclamation-circle-fill fs-5 text-warning"></i>
                        <div>{{ session('warning') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0 d-flex align-items-center gap-2" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                        <div>{{ session('error') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
