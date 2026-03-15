@props([
    'status',
])

   @if ($status)
    <div {{ $attributes->merge(['class' => 'alert alert-success shadow-sm py-2 px-3 small rounded-3 border-0 bg-success bg-opacity-10 text-success fw-medium']) }}>
            <i class="bi bi-info-circle-fill me-2"></i> {{ $status }}
        </div>
@endif
