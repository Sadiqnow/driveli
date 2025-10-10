@props([
    'title' => null,
    'subtitle' => null,
    'headerActions' => null,
    'footerContent' => null,
    'variant' => 'default',
    'size' => 'default',
    'shadow' => true,
    'interactive' => false
])

@php
$classes = [
    'card',
    'shadow' => $shadow ? 'shadow-sm' : '',
    'interactive' => $interactive ? 'card-hover' : '',
    'size' => match($size) {
        'sm' => 'card-sm',
        'lg' => 'card-lg',
        default => ''
    },
    'variant' => match($variant) {
        'primary' => 'card-primary',
        'success' => 'card-success',
        'warning' => 'card-warning',
        'danger' => 'card-danger',
        'info' => 'card-info',
        default => ''
    }
];
@endphp

<div {{ $attributes->merge(['class' => implode(' ', array_filter($classes))]) }}>
    @if($title || $subtitle || $headerActions)
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            @if($title)
                <h3 class="card-title mb-0">{{ $title }}</h3>
            @endif
            @if($subtitle)
                <p class="card-subtitle text-muted mb-0 mt-1">{{ $subtitle }}</p>
            @endif
        </div>
        @if($headerActions)
            <div class="card-tools">
                {{ $headerActions }}
            </div>
        @endif
    </div>
    @endif
    
    <div class="card-body">
        {{ $slot }}
    </div>
    
    @if($footerContent)
    <div class="card-footer">
        {{ $footerContent }}
    </div>
    @endif
</div>