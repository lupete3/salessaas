@props([
    'title',
    'description',
])

<div class="text-center mb-4">
    <h3 class="fw-bold text-dark">{{ $title }}</h3>
    @if($description)
        <p class="text-muted small mb-0">{{ $description }}</p>
    @endif
</div>
