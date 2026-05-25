{{-- Reusable Stat Card Component --}}
@props([
    'label' => '',
    'value' => '',
    'change' => null,             // Optional: percentage change
    'icon' => null,               // Font Awesome icon class
    'trend' => 'neutral',         // neutral, up, down
    'class' => '',
])

@php
    $trendColor = match($trend) {
        'up' => 'text-success',
        'down' => 'text-error',
        default => '',
    };

    $trendIcon = match($trend) {
        'up' => 'fas fa-arrow-trend-up',
        'down' => 'fas fa-arrow-trend-down',
        default => '',
    };
@endphp

<div {{ $attributes->merge(['class' => "stat-card animate-slide-up {$class}"]) }}>
    <div class="flex-between">
        <div>
            @if($label)
                <p class="stat-card-label">{{ $label }}</p>
            @endif
            @if($value)
                <div class="stat-card-value">{{ $value }}</div>
            @endif
        </div>
        @if($icon)
            <div class="text-white opacity-75">
                <i class="{{ $icon }} text-4xl"></i>
            </div>
        @endif
    </div>

    @if($change)
        <div class="stat-card-change {{ $trendColor }}">
            @if($trendIcon)
                <i class="{{ $trendIcon }}"></i>
            @endif
            <span class="ml-2">{{ $change }}</span>
        </div>
    @endif

    {{ $slot }}
</div>
