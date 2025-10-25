@props([
    'id',
    'title' => '',
    'size' => 'md',
    'backdrop' => 'static',
    'keyboard' => true,
    'scrollable' => false,
    'centered' => true,
    'show' => false
])

@php
$modalClasses = ['modal', 'fade'];
if ($show) $modalClasses[] = 'show';
$modalClass = implode(' ', $modalClasses);

$dialogClasses = ['modal-dialog'];
if ($size !== 'md') $dialogClasses[] = 'modal-' . $size;
if ($scrollable) $dialogClasses[] = 'modal-dialog-scrollable';
if ($centered) $dialogClasses[] = 'modal-dialog-centered';
$dialogClass = implode(' ', $dialogClasses);
@endphp

<div class="{{ $modalClass }}" id="{{ $id }}" tabindex="-1" role="dialog"
     aria-labelledby="{{ $id }}Label" aria-hidden="{{ $show ? 'false' : 'true' }}"
     @if($backdrop === 'static') data-bs-backdrop="static" @endif
     @if(!$keyboard) data-bs-keyboard="false" @endif>
    <div class="{{ $dialogClass }}" role="document">
        <div class="modal-content">
            @if($title)
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @endif
            <div class="modal-body">
                {{ $slot }}
            </div>
            @if(isset($footer))
            <div class="modal-footer">
                {{ $footer }}
            </div>
            @endif
        </div>
    </div>
</div>
