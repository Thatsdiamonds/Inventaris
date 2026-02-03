<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Encoders\JpegEncoder;

class ImageOptimizationService
{
    /**
     * Default quality for image compression (lower = smaller file, worse quality)
     * 80 is a good balance for web use
     */
    protected int $defaultQuality = 80;
    
    /**
     * Maximum width for images (to prevent oversized uploads)
     */
    protected int $maxWidth = 1920;
    
    /**
     * Maximum height for images
     */
    protected int $maxHeight = 1080;

    /**
     * Whether to convert to WebP format for better compression
     */
    protected bool $convertToWebp = true;

    /**
     * Optimize and store an uploaded image
     * 
     * @param UploadedFile $file The uploaded file
     * @param string $directory Storage directory
     * @param array $options Optimization options
     * @return string|null The stored file path
     */
    public function optimizeAndStore(UploadedFile $file, string $directory, array $options = []): ?string
    {
        $quality = $options['quality'] ?? $this->defaultQuality;
        $maxWidth = $options['max_width'] ?? $this->maxWidth;
        $maxHeight = $options['max_height'] ?? $this->maxHeight;
        $toWebp = $options['webp'] ?? $this->convertToWebp;
        
        try {
            // Create image instance
            $image = Image::read($file);
            
            // Get current dimensions
            $width = $image->width();
            $height = $image->height();
            
            // Only resize if the image exceeds max dimensions
            if ($width > $maxWidth || $height > $maxHeight) {
                // Use scale down to maintain aspect ratio
                $image = $image->scaleDown(width: $maxWidth, height: $maxHeight);
            }
            
            // Determine filename and extension
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $filename = $this->generateUniqueFilename($originalName);
            
            // Encode and save
            if ($toWebp) {
                $encoded = $image->encode(new WebpEncoder(quality: $quality));
                $path = $directory . '/' . $filename . '.webp';
            } else {
                $encoded = $image->encode(new JpegEncoder(quality: $quality));
                $path = $directory . '/' . $filename . '.jpg';
            }
            
            // Store to disk
            Storage::disk('public')->put($path, (string) $encoded);
            
            return $path;
            
        } catch (\Exception $e) {
            // Log error and fall back to original upload
            \Log::error('Image optimization failed: ' . $e->getMessage());
            return $file->store($directory, 'public');
        }
    }

    /**
     * Generate a thumbnail from a stored image
     * 
     * @param string $originalPath Path to original image
     * @param int $width Thumbnail width
     * @param int $height Thumbnail height
     * @return string|null The thumbnail path
     */
    public function generateThumbnail(string $originalPath, int $width = 150, int $height = 150): ?string
    {
        try {
            $fullPath = Storage::disk('public')->path($originalPath);
            
            if (!file_exists($fullPath)) {
                return null;
            }
            
            $image = Image::read($fullPath);
            
            // Cover crop for consistent thumbnail sizes
            $image = $image->cover($width, $height);
            
            // Generate thumbnail path
            $pathInfo = pathinfo($originalPath);
            $thumbPath = $pathInfo['dirname'] . '/thumbs/' . $pathInfo['filename'] . '_thumb.webp';
            
            // Ensure thumbs directory exists
            $thumbDir = $pathInfo['dirname'] . '/thumbs';
            if (!Storage::disk('public')->exists($thumbDir)) {
                Storage::disk('public')->makeDirectory($thumbDir);
            }
            
            // Encode and save thumbnail
            $encoded = $image->encode(new WebpEncoder(quality: 75));
            Storage::disk('public')->put($thumbPath, (string) $encoded);
            
            return $thumbPath;
            
        } catch (\Exception $e) {
            \Log::error('Thumbnail generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get image URL with optional thumbnail support
     * 
     * @param string|null $path Image path
     * @param bool $thumbnail Whether to get thumbnail
     * @return string|null
     */
    public function getImageUrl(?string $path, bool $thumbnail = false): ?string
    {
        if (!$path) {
            return null;
        }

        if ($thumbnail) {
            $pathInfo = pathinfo($path);
            $thumbPath = $pathInfo['dirname'] . '/thumbs/' . $pathInfo['filename'] . '_thumb.webp';
            
            if (Storage::disk('public')->exists($thumbPath)) {
                return Storage::url($thumbPath);
            }
        }

        return Storage::url($path);
    }

    /**
     * Optimize existing image on disk
     * 
     * @param string $path Path to existing image
     * @param array $options Optimization options
     * @return string|null New optimized path
     */
    public function optimizeExisting(string $path, array $options = []): ?string
    {
        $quality = $options['quality'] ?? $this->defaultQuality;
        $toWebp = $options['webp'] ?? $this->convertToWebp;
        
        try {
            $fullPath = Storage::disk('public')->path($path);
            
            if (!file_exists($fullPath)) {
                return null;
            }
            
            $image = Image::read($fullPath);
            $pathInfo = pathinfo($path);
            
            if ($toWebp) {
                $encoded = $image->encode(new WebpEncoder(quality: $quality));
                $newPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';
            } else {
                $encoded = $image->encode(new JpegEncoder(quality: $quality));
                $newPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.jpg';
            }
            
            // Store optimized version
            Storage::disk('public')->put($newPath, (string) $encoded);
            
            // Delete original if different format
            if ($path !== $newPath) {
                Storage::disk('public')->delete($path);
            }
            
            return $newPath;
            
        } catch (\Exception $e) {
            \Log::error('Image optimization failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete image and its thumbnail
     * 
     * @param string|null $path Image path
     * @return bool
     */
    public function delete(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        try {
            // Delete main image
            Storage::disk('public')->delete($path);
            
            // Delete thumbnail if exists
            $pathInfo = pathinfo($path);
            $thumbPath = $pathInfo['dirname'] . '/thumbs/' . $pathInfo['filename'] . '_thumb.webp';
            Storage::disk('public')->delete($thumbPath);
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Image deletion failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate unique filename
     */
    protected function generateUniqueFilename(string $originalName): string
    {
        // Sanitize original name
        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $originalName);
        $sanitized = substr($sanitized, 0, 50); // Limit length
        
        return $sanitized . '_' . uniqid();
    }

    /**
     * Get estimated file size reduction percentage
     */
    public function getCompressionStats(string $originalPath, string $optimizedPath): array
    {
        $originalSize = Storage::disk('public')->size($originalPath);
        $optimizedSize = Storage::disk('public')->size($optimizedPath);
        
        $reduction = $originalSize > 0 
            ? round((1 - ($optimizedSize / $originalSize)) * 100, 1)
            : 0;
        
        return [
            'original_size' => $originalSize,
            'optimized_size' => $optimizedSize,
            'reduction_percent' => $reduction,
            'bytes_saved' => $originalSize - $optimizedSize,
        ];
    }
}
