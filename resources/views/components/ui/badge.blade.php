@props([
    'variant' => 'primary',
    'size' => 'default',
    'pill' => false,
    'icon' => null
])

@php
$classes = [
    'badge',
    'variant' => match($variant) {
        'primary' => 'bg-primary',
        'secondary' => 'bg-secondary',
        'success' => 'bg-success',
        'danger' => 'bg-danger',
        'warning' => 'bg-warning text-dark',
        'info' => 'bg-info',
        'light' => 'bg-light text-dark',
        'dark' => 'bg-dark',
        default => 'bg-primary'
    },
    'size' => match($size) {
        'sm' => 'badge-sm',
        'lg' => 'badge-lg',
        default => ''
    },
    'pill' => $pill ? 'rounded-pill' : '',
];
@endphp

<span {{ $attributes->merge(['class' => implode(' ', array_filter($classes))]) }}>
    @if($icon)
        <i class="{{ $icon }}" aria-hidden="true"></i>
    @endif
    {{ $slot }}
</span>