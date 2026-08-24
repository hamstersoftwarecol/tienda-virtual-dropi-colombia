@extends('layouts.admin')

@section('title', 'Cupones de Descuento')
@section('page_header', 'Cupones y Promociones (COP)')

@section('content')
<div class="row g-4">
    <!-- Coupons List -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h5 class="fw-bold mb-0 text-dark">Cupones Activos</h5>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light small text-muted text-uppercase">
                        <tr>
                            <th class="ps-4 py-3">Código</th>
                            <th class="py-3">Descuento</th>
                            <th class="py-3">Monto Mínimo (COP)</th>
                            <th class="py-3">Vence</th>
                            <th class="py-3 text-center">Estado</th>
                            <th class="pe-4 py-3 text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($coupons as $coupon)
                            <tr class="border-bottom">
                                <td class="ps-4 py-3">
                                    <span class="badge bg-primary-subtle text-primary font-monospace fs-6 px-3 py-1">
                                        {{ $coupon->code }}
                                    </span>
                                </td>
                                <td class="py-3 fw-bold">
                                    {{ $coupon->type === 'percent' ? $coupon->value . '% OFF' : format_cop($coupon->value) . ' OFF' }}
                                </td>
                                <td class="py-3 small text-muted">
                                    {{ format_cop($coupon->min_amount) }}
                                </td>
                                <td class="py-3 small text-muted">
                                    {{ $coupon->expires_at ? $coupon->expires_at->format('d/m/Y') : 'Sin expiración' }}
                                </td>
                                <td class="py-3 text-center">
                                    @if($coupon->is_active && (!$coupon->expires_at || $coupon->expires_at->isFuture()))
                                        <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-2 py-1">Activo</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1">Inactivo / Vencido</span>
                                    @endif
                                </td>
                                <td class="pe-4 py-3 text-end">
                                    <form action="{{ route('admin.coupons.destroy', $coupon->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este cupón?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger border-0 rounded-circle" title="Eliminar">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No hay cupones creados aún.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="d-flex justify-content-center mt-3">
            {{ $coupons->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <!-- Create Coupon Form -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
            <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">Crear Nuevo Cupón</h5>
            <form action="{{ route('admin.coupons.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="code" class="form-label fw-semibold small">Código del Cupón *</label>
                    <input type="text" name="code" id="code" class="form-control text-uppercase font-monospace @error('code') is-invalid @enderror" value="{{ old('code') }}" required placeholder="Ej: COLOMBIA2026">
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="type" class="form-label fw-semibold small">Tipo de Descuento *</label>
                    <select name="type" id="type" class="form-select" required>
                        <option value="percent" {{ old('type') === 'percent' ? 'selected' : '' }}>Porcentaje (%)</option>
                        <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>Monto Fijo en Pesos ($ COP)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="value" class="form-label fw-semibold small">Valor del Descuento *</label>
                    <input type="number" step="0.01" name="value" id="value" class="form-control @error('value') is-invalid @enderror" value="{{ old('value') }}" required placeholder="Ej: 15 para 15% o 25000 para $25.000 COP">
                    @error('value')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="min_amount" class="form-label fw-semibold small">Monto Mínimo de Compra ($ COP)</label>
                    <input type="number" step="0.01" name="min_amount" id="min_amount" class="form-control" value="{{ old('min_amount', 0) }}" placeholder="Ej: 100000">
                </div>

                <div class="mb-3">
                    <label for="expires_at" class="form-label fw-semibold small">Fecha de Expiración</label>
                    <input type="date" name="expires_at" id="expires_at" class="form-control" value="{{ old('expires_at') }}">
                </div>

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', '1') ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold small" for="is_active">Cupón Activo</label>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary rounded-pill py-2 fw-semibold">
                        <i class="bi bi-plus-lg me-1"></i> Guardar Cupón
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
