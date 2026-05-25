{{-- Reusable Alert Component --}}
@props([
    'type' => 'primary',         // primary, success, warning, error, info
    'dismissible' => false,      // Show close button
    'icon' => null,              // Font Awesome icon
    'title' => null,
    'class' => '',
])

@php
    $alertClass = "alert alert-{$type} animate-slide-down {$class}";

    $defaultIcon = match($type) {
        'success' => 'fas fa-check-circle',
        'warning' => 'fas fa-exclamation-triangle',
        'error' => 'fas fa-times-circle',
        'info' => 'fas fa-info-circle',
        default => 'fas fa-info-circle',
    };

    $icon = $icon ?? $defaultIcon;
@endphp

<div {{ $attributes->merge(['class' => $alertClass]) }} role="alert">
    <div class="flex gap-md">
        @if($icon)
            <div class="flex-shrink-0">
                <i class="{{ $icon }} text-lg"></i>
            </div>
        @endif

        <div class="flex-1">
            @if($title)
                <p class="font-semibold mb-sm">{{ $title }}</p>
            @endif
            <p>{{ $slot }}</p>
        </div>

        @if($dismissible)
            <div class="flex-shrink-0">
                <button
                    type="button"
                    class="btn-ghost btn-sm"
                    onclick="this.parentElement.parentElement.style.display='none';"
                    aria-label="Close alert"
                >
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif
    </div>
</div>
