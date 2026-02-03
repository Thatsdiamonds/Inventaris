<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Image Optimization Settings
    |--------------------------------------------------------------------------
    |
    | Configure the default settings for image optimization.
    |
    */

    // Default image quality (1-100, lower = smaller file size)
    'quality' => env('IMAGE_QUALITY', 80),

    // Maximum dimensions for uploaded images
    'max_width' => env('IMAGE_MAX_WIDTH', 1920),
    'max_height' => env('IMAGE_MAX_HEIGHT', 1080),

    // Thumbnail dimensions
    'thumbnail_width' => env('IMAGE_THUMB_WIDTH', 150),
    'thumbnail_height' => env('IMAGE_THUMB_HEIGHT', 150),

    // Whether to convert images to WebP format
    'convert_to_webp' => env('IMAGE_CONVERT_WEBP', true),

    // Intervention Image driver (gd or imagick)
    'driver' => \Intervention\Image\Drivers\Gd\Driver::class,
];
