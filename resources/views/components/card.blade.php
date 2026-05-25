{{-- Reusable Card Component --}}
@props([
    'variant' => 'default',       // default, outline, premium
    'hover' => true,              // Enable hover effect
    'interactive' => false,       // Show pointer cursor
    'class' => '',
])

@php
    $baseClasses = 'card animate-fade-in';

    $variantClasses = match($variant) {
        'outline' => 'card-outline',
        'premium' => 'card-premium',
        'default' => '',
        default => '',
    };

    $interactiveClass = $interactive ? 'interactive' : '';
    $hoverClass = $hover ? '' : 'hover:shadow-sm hover:no-translate';

    $allClasses = "{$baseClasses} {$variantClasses} {$interactiveClass} {$class}";
@endphp

<div {{ $attributes->merge(['class' => $allClasses]) }}>
    {{ $slot }}
</div>
