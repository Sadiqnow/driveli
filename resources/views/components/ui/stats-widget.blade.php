@props([
    'title',
    'value',
    'icon' => null,
    'variant' => 'info',
    'href' => null,
    'linkText' => 'View Details',
    'trend' => null,
    'trendIcon' => null,
    'description' => null
])

@php
$classes = [
    'small-box',
    'variant' => match($variant) {
        'primary' => 'bg-primary',
        'secondary' => 'bg-secondary',
        'success' => 'bg-success',
        'danger' => 'bg-danger',
        'warning' => 'bg-warning',
        'info' => 'bg-info',
        'dark' => 'bg-dark',
        default => 'bg-info'
    }
];
@endphp

<div class="col-lg-3 col-6">
    <div {{ $attributes->merge(['class' => implode(' ', array_filter($classes))]) }}>
        <div class="inner">
            <h3>{{ $value }}</h3>
            <p>{{ $title }}</p>
            @if($description)
                <small class="d-block mt-1 opacity-75">{{ $description }}</small>
            @endif
        </div>
        
        @if($icon)
            <div class="icon">
                <i class="{{ $icon }}" aria-hidden="true"></i>
            </div>
        @endif
        
        @if($href)
            <a href="{{ $href }}" class="small-box-footer">
                {{ $linkText }} <i class="fas fa-arrow-circle-right" aria-hidden="true"></i>
            </a>
        @endif
        
        @if($trend)
            <div class="small-box-trend">
                @if($trendIcon)
                    <i class="{{ $trendIcon }}" aria-hidden="true"></i>
                @endif
                {{ $trend }}
            </div>
        @endif
    </div>
</div>