{{-- Dynamic Theme Variables - Tenant Customization --}}
@php
  $user = Auth::user();
  $tenant = $user?->tenant ?? null;

  // Get tenant's custom colors or use defaults
  $primaryColor = $tenant?->primary_color ?? '#245a49';      // Default: Dark Green
  $secondaryColor = $tenant?->secondary_color ?? '#FF6B35';  // Default: Orange
  $accentColor = $tenant?->accent_color ?? '#1a3d33';        // Default: Dark Green

  // Lighten/Darken primary color for variations
  // These are calculated versions for hover states
  // In production, consider storing these or using a color library
  $primaryLight = $tenant?->primary_color_light ?? '#2d7a65';
  $primaryDark = $tenant?->primary_color_dark ?? '#1a3d33';
@endphp

<style>
  :root {
    /* TENANT CUSTOM COLORS - Override defaults */
    --color-primary: {{ $primaryColor }};
    --color-primary-light: {{ $primaryLight }};
    --color-primary-dark: {{ $primaryDark }};
    --color-secondary: {{ $secondaryColor }};
    --color-secondary-dark: {{ $accentColor }};
  }

  /* TENANT-SPECIFIC GRADIENT BACKGROUNDS */
  .gradient-primary {
    background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
  }

  .gradient-primary-soft {
    background: linear-gradient(135deg, {{ $primaryColor }}20 0%, {{ $secondaryColor }}20 100%);
  }

  /* TENANT-SPECIFIC STAT CARDS */
  .stat-card {
    background: linear-gradient(135deg, {{ $primaryDark }} 0%, {{ $primaryColor }} 100%);
  }

  /* TENANT-SPECIFIC BUTTONS - Default colors */
  .btn-primary {
    background-color: {{ $primaryColor }};
  }

  .btn-primary:hover {
    background-color: {{ $primaryLight }};
  }

  .btn-primary:active {
    background-color: {{ $primaryDark }};
  }

  .btn-secondary {
    background-color: {{ $secondaryColor }};
  }

  /* TENANT-SPECIFIC ACCENT ELEMENTS */
  .accent-color {
    color: {{ $secondaryColor }};
  }

  .accent-bg {
    background-color: {{ $secondaryColor }};
  }

  .accent-border {
    border-color: {{ $secondaryColor }};
  }

  /* CUSTOM SHADOWS WITH TENANT COLOR */
  .shadow-tenant {
    box-shadow: 0 20px 40px -10px {{ $primaryColor }}25;
  }
</style>
