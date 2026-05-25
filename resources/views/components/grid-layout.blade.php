{{-- Responsive Grid Layout Component --}}
@props([
    'cols' => 3,                  // Default columns: 1, 2, 3, 4, 6
    'gap' => 'lg',                // Gap size: sm, md, lg
    'class' => '',
])

@php
    $colsClass = "grid-cols-{$cols}";
    $gapClass = "grid-gap-{$gap}";
    $allClasses = "grid {$colsClass} {$gapClass} {$class}";
@endphp

<div {{ $attributes->merge(['class' => $allClasses]) }}>
    {{ $slot }}
</div>
