{{-- Reusable Badge Component --}}
@props([
    'type' => 'primary',         // primary, success, warning, error, info, neutral
    'size' => 'md',              // sm, md, lg
    'icon' => null,              // Font Awesome icon
    'class' => '',
])

@php
    $baseClass = 'badge';

    $typeClass = match($type) {
        'primary' => 'badge-primary',
        'success' => 'badge-success',
        'warning' => 'badge-warning',
        'error' => 'badge-error',
        default => '',
    };

    $sizeClass = match($size) {
        'sm' => 'text-xs px-sm py-xs',
        'lg' => 'text-base px-lg py-sm',
        'md' => '',
        default => '',
    };

    $allClasses = "{$baseClass} {$typeClass} {$sizeClass} {$class}";
@endphp

<span {{ $attributes->merge(['class' => $allClasses]) }}>
    @if($icon)
        <i class="{{ $icon }} mr-xs"></i>
    @endif
    {{ $slot }}
</span>
