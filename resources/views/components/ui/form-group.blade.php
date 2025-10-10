@props([
    'label' => null,
    'for' => null,
    'required' => false,
    'error' => null,
    'help' => null,
    'inline' => false
])

@php
$groupClasses = $inline ? 'form-group row' : 'form-group mb-3';
$labelClasses = $inline ? 'col-form-label col-md-3' : 'form-label';
$inputWrapperClasses = $inline ? 'col-md-9' : '';
@endphp

<div class="{{ $groupClasses }}">
    @if($label)
    <label @if($for) for="{{ $for }}" @endif 
           class="{{ $labelClasses }}">
        {{ $label }}
        @if($required)
            <span class="text-danger" aria-label="Required field">*</span>
        @endif
    </label>
    @endif
    
    <div class="{{ $inputWrapperClasses }}">
        {{ $slot }}
        
        @if($error)
            <div class="invalid-feedback d-block" role="alert">
                <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                {{ $error }}
            </div>
        @endif
        
        @if($help && !$error)
            <div class="form-text">
                <i class="fas fa-info-circle" aria-hidden="true"></i>
                {{ $help }}
            </div>
        @endif
    </div>
</div>