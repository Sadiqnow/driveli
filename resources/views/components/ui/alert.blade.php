@props([
    'variant' => 'info',
    'dismissible' => false,
    'icon' => null,
    'title' => null
])

@php
$classes = [
    'alert',
    'variant' => match($variant) {
        'primary' => 'alert-primary',
        'secondary' => 'alert-secondary',
        'success' => 'alert-success',
        'danger' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info',
        'light' => 'alert-light',
        'dark' => 'alert-dark',
        default => 'alert-info'
    },
    'dismissible' => $dismissible ? 'alert-dismissible fade show' : '',
];

$defaultIcons = [
    'success' => 'fas fa-check-circle',
    'danger' => 'fas fa-exclamation-triangle',
    'warning' => 'fas fa-exclamation-circle',
    'info' => 'fas fa-info-circle',
];

$displayIcon = $icon ?: ($defaultIcons[$variant] ?? null);
@endphp

<div {{ $attributes->merge(['class' => implode(' ', array_filter($classes)), 'role' => 'alert']) }}>
    <div class="d-flex align-items-start">
        @if($displayIcon)
            <div class="me-3 mt-1">
                <i class="{{ $displayIcon }}" aria-hidden="true"></i>
            </div>
        @endif
        
        <div class="flex-grow-1">
            @if($title)
                <h5 class="alert-heading mb-2">{{ $title }}</h5>
            @endif
            {{ $slot }}
        </div>
        
        @if($dismissible)
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        @endif
    </div>
</div>