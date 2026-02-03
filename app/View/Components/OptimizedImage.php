<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Storage;
use Illuminate\View\Component;

class OptimizedImage extends Component
{
    public string $src;
    public ?string $fallback;
    public ?string $alt;
    public ?string $class;
    public ?int $width;
    public ?int $height;
    public bool $lazy;

    /**
     * Create a new component instance.
     */
    public function __construct(
        ?string $path = null,
        ?string $fallback = null,
        ?string $alt = '',
        ?string $class = null,
        ?int $width = null,
        ?int $height = null,
        bool $lazy = true
    ) {
        $this->alt = $alt ?? '';
        $this->class = $class;
        $this->width = $width;
        $this->height = $height;
        $this->lazy = $lazy;
        $this->fallback = $fallback;

        // Determine the image source
        if ($path && Storage::disk('public')->exists($path)) {
            $this->src = Storage::url($path);
        } elseif ($fallback) {
            $this->src = $fallback;
        } else {
            // Default placeholder
            $this->src = $this->generatePlaceholder();
        }
    }

    /**
     * Generate an SVG placeholder
     */
    private function generatePlaceholder(): string
    {
        $width = $this->width ?? 150;
        $height = $this->height ?? 150;
        
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '">';
        $svg .= '<rect fill="#f3f4f6" width="100%" height="100%"/>';
        $svg .= '<text fill="#9ca3af" font-family="sans-serif" font-size="14" x="50%" y="50%" text-anchor="middle" dy=".3em">No Image</text>';
        $svg .= '</svg>';
        
        return 'data:image/svg+xml,' . rawurlencode($svg);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.optimized-image');
    }
}
