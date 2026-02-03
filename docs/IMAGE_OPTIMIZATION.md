# Panduan Optimisasi Gambar & Performa Sistem

## Fitur Baru yang Ditambahkan

### 1. Sistem Kompresi Gambar Otomatis

Menggunakan package `intervention/image-laravel` untuk:
- **Kompresi otomatis** saat upload gambar
- **Konversi ke WebP** untuk ukuran file lebih kecil (hingga 70% lebih kecil)
- **Resize otomatis** untuk mencegah gambar yang terlalu besar
- **Lazy loading** untuk performa tampilan lebih cepat

### 2. Commands untuk Optimisasi

#### Optimisasi Gambar yang Sudah Ada
```bash
# Melihat preview (dry run)
php artisan images:optimize --dry-run

# Optimisasi folder items
php artisan images:optimize --directory=items

# Optimisasi folder settings
php artisan images:optimize --directory=settings

# Optimisasi semua folder
php artisan images:optimize --directory=all

# Custom quality (default: 80)
php artisan images:optimize --quality=75
```

#### Optimisasi Sistem (Production)
```bash
# Clear dan rebuild semua cache
php artisan system:optimize --clear

# Rebuild cache tanpa clear
php artisan system:optimize
```

---

## Konfigurasi

### File Konfigurasi: `config/image.php`

```php
return [
    'quality' => env('IMAGE_QUALITY', 80),
    'max_width' => env('IMAGE_MAX_WIDTH', 1920),
    'max_height' => env('IMAGE_MAX_HEIGHT', 1080),
    'thumbnail_width' => env('IMAGE_THUMB_WIDTH', 150),
    'thumbnail_height' => env('IMAGE_THUMB_HEIGHT', 150),
    'convert_to_webp' => env('IMAGE_CONVERT_WEBP', true),
];
```

### Environment Variables (`.env`)

```env
IMAGE_QUALITY=80
IMAGE_MAX_WIDTH=1920
IMAGE_MAX_HEIGHT=1080
IMAGE_CONVERT_WEBP=true
```

---

## Penggunaan Komponen

### Blade Component untuk Gambar Optimized

```blade
{{-- Basic usage --}}
<x-optimized-image 
    :path="$item->photo_path" 
    alt="Item Photo"
/>

{{-- With custom size --}}
<x-optimized-image 
    :path="$item->photo_path" 
    alt="Item Photo"
    :width="200"
    :height="200"
    class="rounded-lg"
/>

{{-- Disable lazy loading --}}
<x-optimized-image 
    :path="$item->photo_path" 
    :lazy="false"
/>
```

---

## Service Usage (Programmatic)

```php
use App\Services\ImageOptimizationService;

// Via dependency injection
public function store(Request $request, ImageOptimizationService $imageService)
{
    if ($request->hasFile('photo')) {
        $path = $imageService->optimizeAndStore(
            $request->file('photo'),
            'items',
            [
                'quality' => 80,
                'max_width' => 1200, 
                'max_height' => 1200,
                'webp' => true
            ]
        );
    }
}

// Generate thumbnail
$thumbPath = $imageService->generateThumbnail($originalPath, 150, 150);

// Get URL (with optional thumbnail)
$url = $imageService->getImageUrl($path, thumbnail: true);

// Delete image and thumbnail
$imageService->delete($path);
```

---

## Optimisasi Performa yang Diterapkan

### 1. Model-Level Caching
- `Category` dan `Location` menggunakan `HasCache` trait
- `Setting` memiliki caching khusus dengan auto-invalidation

### 2. Query Optimization
- `preventLazyLoading()` aktif di development untuk mendeteksi N+1 queries
- Query logging aktif di development

### 3. Response Optimization
- Cache headers untuk static assets
- Security headers (X-Content-Type-Options, X-Frame-Options, etc.)

### 4. Image Optimization
- WebP conversion (70% lebih kecil dari JPEG)
- Automatic resizing untuk mencegah file besar
- Lazy loading di browser

---

## Rekomendasi untuk Production

1. **Jalankan optimisasi sistem:**
   ```bash
   php artisan system:optimize --clear
   ```

2. **Optimisasi gambar yang sudah ada:**
   ```bash
   php artisan images:optimize --directory=all
   ```

3. **Gunakan caching:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

4. **Composer optimization:**
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

---

## Perbandingan Performa

| Aspek | Sebelum | Sesudah |
|-------|---------|---------|
| Ukuran gambar rata-rata | ~500KB | ~100KB |
| Format gambar | JPEG/PNG | WebP |
| Query database per request | Tidak di-cache | Di-cache 1 jam |
| Response headers | Minimal | Teroptimasi |
| Lazy loading gambar | Tidak | Ya |

---

## Troubleshooting

### Gambar tidak muncul setelah optimisasi
1. Pastikan storage link sudah dibuat:
   ```bash
   php artisan storage:link
   ```

2. Periksa permission folder `storage/app/public`

### WebP tidak didukung di browser lama
- WebP didukung di semua browser modern
- Untuk browser lama, fallback akan digunakan otomatis

### Cache tidak ter-invalidate
```bash
php artisan cache:clear
```

---

## Struktur File Baru

```
app/
├── Console/Commands/
│   ├── OptimizeExistingImages.php  # Command untuk optimisasi gambar
│   └── OptimizeSystem.php          # Command untuk optimisasi sistem
├── Http/Middleware/
│   └── OptimizeResponse.php        # Middleware response optimization
├── Services/
│   └── ImageOptimizationService.php # Service untuk kompresi gambar
├── Traits/
│   └── HasCache.php                # Trait untuk model caching
└── View/Components/
    └── OptimizedImage.php          # Blade component

config/
└── image.php                       # Konfigurasi image optimization

resources/views/components/
└── optimized-image.blade.php       # View component
```
