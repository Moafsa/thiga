{{-- Reusable Button Component --}}
@props([
    'variant' => 'primary',        // primary, secondary, outline, ghost
    'size' => 'md',               // sm, md, lg
    'disabled' => false,
    'type' => 'button',           // button, submit, reset
    'icon' => null,               // Icon class or name
    'class' => '',
    'href' => null,               // If provided, renders as <a> tag
])

@php
    $baseClasses = 'btn animate-fade-in';

    $variantClasses = match($variant) {
        'primary' => 'btn-primary',
        'secondary' => 'btn-secondary',
        'outline' => 'btn-outline',
        'ghost' => 'btn-ghost',
        default => 'btn-primary',
    };

    $sizeClasses = match($size) {
        'sm' => 'btn-sm',
        'lg' => 'btn-lg',
        'md' => '',
        default => '',
    };

    $disabledAttr = $disabled ? 'disabled' : '';
    $allClasses = "{$baseClasses} {$variantClasses} {$sizeClasses} {$class}";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $allClasses]) }}>
        @if($icon)
            <i class="{{ $icon }}"></i>
        @endif
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        {{ $disabledAttr }}
        {{ $attributes->merge(['class' => $allClasses]) }}
    >
        @if($icon)
            <i class="{{ $icon }}"></i>
        @endif
        {{ $slot }}
    </button>
@endif
