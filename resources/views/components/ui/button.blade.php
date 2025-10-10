@props([
    'variant' => 'primary',
    'size' => 'default',
    'icon' => null,
    'iconPosition' => 'left',
    'loading' => false,
    'disabled' => false,
    'href' => null,
    'type' => 'button'
])

@php
$classes = [
    'btn',
    'variant' => match($variant) {
        'primary' => 'btn-primary',
        'secondary' => 'btn-secondary',
        'success' => 'btn-success',
        'danger' => 'btn-danger',
        'warning' => 'btn-warning',
        'info' => 'btn-info',
        'light' => 'btn-light',
        'dark' => 'btn-dark',
        'outline-primary' => 'btn-outline-primary',
        'outline-secondary' => 'btn-outline-secondary',
        'outline-success' => 'btn-outline-success',
        'outline-danger' => 'btn-outline-danger',
        'outline-warning' => 'btn-outline-warning',
        'outline-info' => 'btn-outline-info',
        default => 'btn-primary'
    },
    'size' => match($size) {
        'sm' => 'btn-sm',
        'lg' => 'btn-lg',
        default => ''
    },
    'loading' => $loading ? 'loading' : '',
];

$isDisabled = $disabled || $loading;
@endphp

@if($href)
    <a href="{{ $href }}" 
       {{ $attributes->merge(['class' => implode(' ', array_filter($classes))]) }}
       @if($isDisabled) aria-disabled="true" tabindex="-1" @endif>
        @if($loading)
            <i class="fas fa-spinner fa-spin" aria-hidden="true"></i>
        @elseif($icon && $iconPosition === 'left')
            <i class="{{ $icon }}" aria-hidden="true"></i>
        @endif
        
        {{ $slot }}
        
        @if($icon && $iconPosition === 'right')
            <i class="{{ $icon }}" aria-hidden="true"></i>
        @endif
    </a>
@else
    <button type="{{ $type }}" 
            {{ $attributes->merge(['class' => implode(' ', array_filter($classes))]) }}
            @if($isDisabled) disabled @endif>
        @if($loading)
            <i class="fas fa-spinner fa-spin" aria-hidden="true"></i>
        @elseif($icon && $iconPosition === 'left')
            <i class="{{ $icon }}" aria-hidden="true"></i>
        @endif
        
        {{ $slot }}
        
        @if($icon && $iconPosition === 'right')
            <i class="{{ $icon }}" aria-hidden="true"></i>
        @endif
    </button>
@endif