<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Contracts\Cache\Repository as Cache;

class ThemeService
{
    /**
     * Default color palette
     */
    const DEFAULT_COLORS = [
        'primary' => '#245a49',           // Dark Green
        'primary_light' => '#2d7a65',     // Light Green
        'primary_dark' => '#1a3d33',      // Dark Green variant
        'secondary' => '#FF6B35',         // Orange
        'secondary_light' => '#FFB347',   // Light Orange
        'secondary_dark' => '#E55A2B',    // Dark Orange
    ];

    /**
     * Theme presets available
     */
    const PRESETS = [
        'default' => [
            'name' => 'Default Green',
            'primary' => '#245a49',
            'secondary' => '#FF6B35',
            'accent' => '#1a3d33',
        ],
        'modern' => [
            'name' => 'Modern Blue',
            'primary' => '#0066CC',
            'secondary' => '#FF6B35',
            'accent' => '#004199',
        ],
        'premium' => [
            'name' => 'Premium Purple',
            'primary' => '#6B5B95',
            'secondary' => '#FF6B35',
            'accent' => '#4A3F6B',
        ],
        'minimal' => [
            'name' => 'Minimal Black',
            'primary' => '#1A1A1A',
            'secondary' => '#FF6B35',
            'accent' => '#000000',
        ],
        'vibrant' => [
            'name' => 'Vibrant Red',
            'primary' => '#E63946',
            'secondary' => '#FFB703',
            'accent' => '#A71C2F',
        ],
    ];

    protected Cache $cache;

    /**
     * Constructor
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get theme colors for a specific tenant
     *
     * @param Tenant|null $tenant
     * @return array
     */
    public function getThemeColors(?Tenant $tenant = null): array
    {
        if (!$tenant) {
            return self::DEFAULT_COLORS;
        }

        $cacheKey = "theme.colors.tenant.{$tenant->id}";

        return $this->cache->remember($cacheKey, 3600, function () use ($tenant) {
            return [
                'primary' => $tenant->primary_color ?? self::DEFAULT_COLORS['primary'],
                'primary_light' => $tenant->primary_color_light ?? self::DEFAULT_COLORS['primary_light'],
                'primary_dark' => $tenant->primary_color_dark ?? self::DEFAULT_COLORS['primary_dark'],
                'secondary' => $tenant->secondary_color ?? self::DEFAULT_COLORS['secondary'],
                'secondary_light' => $tenant->secondary_color_light ?? self::DEFAULT_COLORS['secondary_light'],
                'secondary_dark' => $tenant->secondary_color_dark ?? self::DEFAULT_COLORS['secondary_dark'],
            ];
        });
    }

    /**
     * Get all available theme presets
     *
     * @return array
     */
    public function getPresets(): array
    {
        return self::PRESETS;
    }

    /**
     * Apply a theme preset to a tenant
     *
     * @param Tenant $tenant
     * @param string $presetKey
     * @return bool
     */
    public function applyPreset(Tenant $tenant, string $presetKey): bool
    {
        if (!isset(self::PRESETS[$presetKey])) {
            return false;
        }

        $preset = self::PRESETS[$presetKey];

        return $tenant->update([
            'primary_color' => $preset['primary'] ?? self::DEFAULT_COLORS['primary'],
            'secondary_color' => $preset['secondary'] ?? self::DEFAULT_COLORS['secondary'],
            'accent_color' => $preset['accent'] ?? self::DEFAULT_COLORS['secondary'],
            'theme_preset' => $presetKey,
        ]) && $this->clearThemeCache($tenant);
    }

    /**
     * Set custom colors for a tenant
     *
     * @param Tenant $tenant
     * @param array $colors
     * @return bool
     */
    public function setCustomColors(Tenant $tenant, array $colors): bool
    {
        $updateData = [];

        // Validate and update primary color
        if (isset($colors['primary']) && $this->isValidColor($colors['primary'])) {
            $updateData['primary_color'] = $colors['primary'];
        }

        // Validate and update secondary color
        if (isset($colors['secondary']) && $this->isValidColor($colors['secondary'])) {
            $updateData['secondary_color'] = $colors['secondary'];
        }

        // Validate and update accent color
        if (isset($colors['accent']) && $this->isValidColor($colors['accent'])) {
            $updateData['accent_color'] = $colors['accent'];
        }

        // Mark as custom theme
        if (!empty($updateData)) {
            $updateData['theme_preset'] = 'custom';
            return $tenant->update($updateData) && $this->clearThemeCache($tenant);
        }

        return false;
    }

    /**
     * Get CSS variables string for inline styles
     *
     * @param Tenant|null $tenant
     * @return string
     */
    public function getCSSVariables(?Tenant $tenant = null): string
    {
        $colors = $this->getThemeColors($tenant);

        $css = ":root {\n";
        $css .= "  --color-primary: {$colors['primary']};\n";
        $css .= "  --color-primary-light: {$colors['primary_light']};\n";
        $css .= "  --color-primary-dark: {$colors['primary_dark']};\n";
        $css .= "  --color-secondary: {$colors['secondary']};\n";
        $css .= "  --color-secondary-light: {$colors['secondary_light']};\n";
        $css .= "  --color-secondary-dark: {$colors['secondary_dark']};\n";
        $css .= "}\n";

        return $css;
    }

    /**
     * Validate if a string is a valid hex color
     *
     * @param string $color
     * @return bool
     */
    protected function isValidColor(string $color): bool
    {
        return preg_match('/^#[a-fA-F0-9]{6}$/', $color) === 1;
    }

    /**
     * Clear theme cache for a specific tenant
     *
     * @param Tenant $tenant
     * @return bool
     */
    public function clearThemeCache(Tenant $tenant): bool
    {
        return $this->cache->forget("theme.colors.tenant.{$tenant->id}");
    }

    /**
     * Clear theme cache for all tenants
     *
     * @return bool
     */
    public function clearAllThemeCache(): bool
    {
        // This is a simplified approach. In production, you might want to use tags
        return true;
    }

    /**
     * Lighten a color by a percentage
     *
     * @param string $color
     * @param int $percent
     * @return string
     */
    public function lightenColor(string $color, int $percent = 10): string
    {
        $color = ltrim($color, '#');
        $color = hexdec($color);

        $r = ($color >> 16) & 0xFF;
        $g = ($color >> 8) & 0xFF;
        $b = $color & 0xFF;

        $r = intval($r + (255 - $r) * ($percent / 100));
        $g = intval($g + (255 - $g) * ($percent / 100));
        $b = intval($b + (255 - $b) * ($percent / 100));

        $r = min(255, max(0, $r));
        $g = min(255, max(0, $g));
        $b = min(255, max(0, $b));

        return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) .
                     str_pad(dechex($g), 2, '0', STR_PAD_LEFT) .
                     str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
    }

    /**
     * Darken a color by a percentage
     *
     * @param string $color
     * @param int $percent
     * @return string
     */
    public function darkenColor(string $color, int $percent = 10): string
    {
        $color = ltrim($color, '#');
        $color = hexdec($color);

        $r = ($color >> 16) & 0xFF;
        $g = ($color >> 8) & 0xFF;
        $b = $color & 0xFF;

        $r = intval($r * (1 - $percent / 100));
        $g = intval($g * (1 - $percent / 100));
        $b = intval($b * (1 - $percent / 100));

        return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) .
                     str_pad(dechex($g), 2, '0', STR_PAD_LEFT) .
                     str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
    }

    /**
     * Get a contrasting text color (black or white) based on background color
     *
     * @param string $backgroundColor
     * @return string
     */
    public function getContrastingTextColor(string $backgroundColor): string
    {
        $color = ltrim($backgroundColor, '#');
        $color = hexdec($color);

        $r = ($color >> 16) & 0xFF;
        $g = ($color >> 8) & 0xFF;
        $b = $color & 0xFF;

        // Calculate luminance
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

        // Return black text for light backgrounds, white text for dark backgrounds
        return $luminance > 0.5 ? '#000000' : '#FFFFFF';
    }
}
