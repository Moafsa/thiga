{{-- Dynamic Theme Variables - Tenant Customization --}}
@php
  $user = Auth::user();
  $tenant = ($tenant ?? null) ?? (function_exists('tenant') ? tenant() : null) ?? $user?->tenant ?? null;

  // Get tenant's custom colors or use defaults
  $primaryColor = $tenant?->primary_color ?? '#245a49';      // Default: Dark Green
  $secondaryColor = $tenant?->secondary_color ?? '#1a3d33';  // Default: Dark Green/Teal (sidebar bg)
  $accentColor = $tenant?->accent_color ?? '#FF6B35';        // Default: Orange

  // Convert hex color to RGB string helper
  if (!function_exists('hex2rgb')) {
      function hex2rgb($hex) {
          $hex = str_replace("#", "", $hex);
          if (strlen($hex) == 3) {
              $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
              $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
              $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
          } else {
              $r = hexdec(substr($hex, 0, 2));
              $g = hexdec(substr($hex, 2, 2));
              $b = hexdec(substr($hex, 4, 2));
          }
          return "$r, $g, $b";
      }
  }

  // Adjust brightness helper to dynamically calculate hover states
  if (!function_exists('adjustBrightness')) {
      function adjustBrightness($hex, $steps) {
          $steps = max(-255, min(255, $steps));
          $hex = str_replace('#', '', $hex);
          if (strlen($hex) == 3) {
              $hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);
          }
          $color_parts = str_split($hex, 2);
          $return = '#';
          foreach ($color_parts as $color) {
              $dec = hexdec($color);
              $dec = max(0, min(255, $dec + $steps));
              $return .= str_pad(dechex($dec), 2, '0', STR_PAD_LEFT);
          }
          return $return;
      }
  }

  // Lighten/Darken primary and accent colors for variations
  $primaryLight = adjustBrightness($primaryColor, 35);
  $primaryDark = adjustBrightness($primaryColor, -25);
  $accentLight = adjustBrightness($accentColor, 30);
  $accentDark = adjustBrightness($accentColor, -20);
@endphp

<style>
  :root {
    /* TENANT CUSTOM COLORS - Override defaults */
    --color-primary: {{ $primaryColor }};
    --color-primary-light: {{ $primaryLight }};
    --color-primary-dark: {{ $secondaryColor }}; /* Use secondary color as primary dark */
    --color-secondary: {{ $accentColor }};       /* Use accent color as secondary/highlight */
    --color-secondary-light: {{ $accentLight }};
    --color-secondary-dark: {{ $accentDark }};

    /* Dynamic RGB variables for transparent overlay usage */
    --color-primary-rgb: {{ hex2rgb($primaryColor) }};
    --color-secondary-rgb: {{ hex2rgb($accentColor) }};
    --color-accent-rgb: {{ hex2rgb($accentColor) }};

    /* Retrocompatibility for old style variable names */
    --cor-principal: {{ $primaryColor }};
    --cor-secundaria: {{ $secondaryColor }};
    --cor-acento: {{ $accentColor }};

    --cor-principal-rgb: {{ hex2rgb($primaryColor) }};
    --cor-secundaria-rgb: {{ hex2rgb($secondaryColor) }};
    --cor-acento-rgb: {{ hex2rgb($accentColor) }};
  }

  /* TENANT-SPECIFIC GRADIENT BACKGROUNDS */
  .gradient-primary {
    background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $accentColor }} 100%);
  }

  .gradient-primary-soft {
    background: linear-gradient(135deg, rgba({{ hex2rgb($primaryColor) }}, 0.15) 0%, rgba({{ hex2rgb($accentColor) }}, 0.15) 100%);
  }

  /* TENANT-SPECIFIC STAT CARDS */
  .stat-card {
    background: linear-gradient(135deg, {{ $secondaryColor }} 0%, {{ $primaryColor }} 100%);
  }

  /* TENANT-SPECIFIC BUTTONS - Default colors */
  .btn-primary {
    background-color: {{ $accentColor }};
    color: #ffffff;
  }

  .btn-primary:hover {
    background-color: {{ $accentLight }};
    color: #ffffff;
  }

  .btn-primary:active {
    background-color: {{ $accentDark }};
    color: #ffffff;
  }

  .btn-secondary {
    background-color: {{ $secondaryColor }};
    color: #ffffff;
  }

  .btn-secondary:hover {
    background-color: {{ $primaryLight }};
    color: #ffffff;
  }

  /* TENANT-SPECIFIC ACCENT ELEMENTS */
  .accent-color {
    color: {{ $accentColor }};
  }

  .accent-bg {
    background-color: {{ $accentColor }};
  }

  .accent-border {
    border-color: {{ $accentColor }};
  }

  /* CUSTOM SHADOWS WITH TENANT COLOR */
  .shadow-tenant {
    box-shadow: 0 20px 40px -10px rgba({{ hex2rgb($primaryColor) }}, 0.15);
  }
</style>

