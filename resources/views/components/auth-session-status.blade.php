@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'alert alert-info py-2 small mb-3 rounded-3']) }}>
        <i class="bi bi-info-circle me-1"></i> {{ $status }}
    </div>
@endif
