<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizer
{
    protected string $disk;
    protected int $quality;
    protected string $format;

    public function __construct()
    {
        $this->disk = $this->getStorageDisk();
        $this->quality = (int) config('filesystems.image_quality', 80);
        $this->format = config('filesystems.image_format', 'webp');
    }

    /**
     * Get the active storage disk
     */
    protected function getStorageDisk(): string
    {
        if (
            !empty(config('filesystems.disks.s3.key'))
            && !empty(config('filesystems.disks.s3.secret'))
            && !empty(config('filesystems.disks.s3.bucket'))
        ) {
            return 's3';
        }

        return 'public';
    }

    /**
     * Responsive image variant sizes
     */
    protected array $variants = [
        'thumb'  => ['width' => 150, 'height' => 150],
        'small'  => ['width' => 400, 'height' => 400],
        'medium' => ['width' => 800, 'height' => 600],
        'large'  => ['width' => 1200, 'height' => 900],
    ];

    /**
     * Upload and optimize an image file to WebP with responsive variants
     */
    public function optimizeAndUpload(UploadedFile $file, string $directory = 'images', array $options = []): array
    {
        $quality = $options['quality'] ?? $this->quality;
        $maxWidth = $options['max_width'] ?? (int) config('filesystems.max_image_width', 2048);
        $maxHeight = $options['max_height'] ?? (int) config('filesystems.max_image_height', 2048);

        // Read the source image
        $sourceImage = $this->createImageFromFile($file->getRealPath(), $file->getMimeType());

        if (!$sourceImage) {
            throw new \RuntimeException('Không thể đọc file hình ảnh');
        }

        // Get original dimensions
        $origWidth = imagesx($sourceImage);
        $origHeight = imagesy($sourceImage);

        // Calculate resize dimensions (maintain aspect ratio)
        [$newWidth, $newHeight] = $this->calculateDimensions($origWidth, $origHeight, $maxWidth, $maxHeight);

        // Resize if needed
        if ($newWidth !== $origWidth || $newHeight !== $origHeight) {
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            // Preserve transparency for WebP
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
            imagedestroy($sourceImage);
            $sourceImage = $resized;
        }

        // Encode to WebP
        ob_start();
        imagewebp($sourceImage, null, $quality);
        $webpData = ob_get_clean();

        if (!$webpData) {
            imagedestroy($sourceImage);
            throw new \RuntimeException('Không thể convert sang WebP');
        }

        // Generate filename
        $timestamp = now()->format('Ymd_His');
        $random = Str::random(8);
        $filename = "{$timestamp}_{$random}.webp";
        $path = $directory . '/' . $filename;

        // Store original to disk
        Storage::disk($this->disk)->put($path, $webpData, 'public');

        // Generate responsive variants
        $variantUrls = $this->createResponsiveVariants(
            $sourceImage, $directory, $timestamp, $random, $quality
        );

        imagedestroy($sourceImage);

        $url = $this->getFileUrl($path);

        return [
            'path' => $path,
            'url' => $url,
            'thumbnail_url' => $variantUrls['thumb'] ?? null,
            'variants' => $variantUrls,
            'disk' => $this->disk,
            'size' => strlen($webpData),
            'width' => $newWidth,
            'height' => $newHeight,
            'format' => 'webp',
            'original_name' => $file->getClientOriginalName(),
        ];
    }

    /**
     * Convert a base64 image to WebP and upload
     */
    public function optimizeBase64AndUpload(string $base64, string $directory = 'images', array $options = []): array
    {
        $quality = $options['quality'] ?? $this->quality;
        $maxWidth = $options['max_width'] ?? (int) config('filesystems.max_image_width', 2048);
        $maxHeight = $options['max_height'] ?? (int) config('filesystems.max_image_height', 2048);

        // Decode base64
        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
            $base64 = substr($base64, strpos($base64, ',') + 1);
        }

        $rawData = base64_decode($base64);
        if ($rawData === false) {
            throw new \InvalidArgumentException('Invalid base64 data');
        }

        // Create GD image from raw data
        $sourceImage = @imagecreatefromstring($rawData);
        if (!$sourceImage) {
            throw new \RuntimeException('Không thể đọc dữ liệu hình ảnh từ base64');
        }

        // Get original dimensions
        $origWidth = imagesx($sourceImage);
        $origHeight = imagesy($sourceImage);

        // Calculate resize dimensions
        [$newWidth, $newHeight] = $this->calculateDimensions($origWidth, $origHeight, $maxWidth, $maxHeight);

        // Resize if needed
        if ($newWidth !== $origWidth || $newHeight !== $origHeight) {
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
            imagedestroy($sourceImage);
            $sourceImage = $resized;
        }

        // Encode to WebP
        ob_start();
        imagewebp($sourceImage, null, $quality);
        $webpData = ob_get_clean();
        imagedestroy($sourceImage);

        if (!$webpData) {
            throw new \RuntimeException('Không thể convert sang WebP');
        }

        // Generate filename
        $timestamp = now()->format('Ymd_His');
        $random = Str::random(8);
        $filename = "{$timestamp}_{$random}.webp";
        $path = $directory . '/' . $filename;

        // Store to disk
        Storage::disk($this->disk)->put($path, $webpData, 'public');

        // Generate thumbnail
        $thumbnailUrl = null;
        if ($options['thumbnail'] ?? true) {
            $thumbnailUrl = $this->createThumbnail($webpData, $directory, $timestamp, $random, $quality);
        }

        $url = $this->getFileUrl($path);

        return [
            'path' => $path,
            'url' => $url,
            'thumbnail_url' => $thumbnailUrl,
            'disk' => $this->disk,
            'size' => strlen($webpData),
            'width' => $newWidth,
            'height' => $newHeight,
            'format' => 'webp',
        ];
    }

    /**
     * Create responsive image variants from a GD image resource
     */
    protected function createResponsiveVariants(\GdImage $sourceImage, string $directory, string $timestamp, string $random, int $quality): array
    {
        $origW = imagesx($sourceImage);
        $origH = imagesy($sourceImage);
        $urls = [];

        foreach ($this->variants as $name => $dims) {
            // Skip variants larger than original
            if ($dims['width'] >= $origW && $dims['height'] >= $origH) {
                continue;
            }

            [$tw, $th] = $this->calculateDimensions($origW, $origH, $dims['width'], $dims['height']);

            $variant = imagecreatetruecolor($tw, $th);
            imagealphablending($variant, false);
            imagesavealpha($variant, true);
            imagecopyresampled($variant, $sourceImage, 0, 0, 0, 0, $tw, $th, $origW, $origH);

            ob_start();
            imagewebp($variant, null, $quality);
            $variantData = ob_get_clean();
            imagedestroy($variant);

            if (!$variantData) {
                continue;
            }

            $variantPath = $directory . '/' . $name . '/' . "{$timestamp}_{$random}.webp";
            Storage::disk($this->disk)->put($variantPath, $variantData, 'public');
            $urls[$name] = $this->getFileUrl($variantPath);
        }

        return $urls;
    }

    /**
     * Calculate new dimensions maintaining aspect ratio
     */
    protected function calculateDimensions(int $origWidth, int $origHeight, int $maxWidth, int $maxHeight): array
    {
        $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);

        // Don't upscale
        if ($ratio >= 1) {
            return [$origWidth, $origHeight];
        }

        return [
            (int) round($origWidth * $ratio),
            (int) round($origHeight * $ratio),
        ];
    }

    /**
     * Create GD image from file
     */
    protected function createImageFromFile(string $path, ?string $mimeType): ?\GdImage
    {
        // SVG and other non-raster formats cannot be handled by GD
        $unsupported = ['image/svg+xml', 'image/svg', 'image/tiff', 'image/x-icon'];
        if (in_array($mimeType, $unsupported, true)) {
            return null;
        }

        $image = match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/gif' => @imagecreatefromgif($path),
            'image/webp' => @imagecreatefromwebp($path),
            'image/bmp' => @imagecreatefrombmp($path),
            default => @imagecreatefromstring(file_get_contents($path)),
        };

        return $image instanceof \GdImage ? $image : null;
    }

    /**
     * Get file URL from path
     */
    protected function getFileUrl(string $path): string
    {
        if ($this->disk === 's3') {
            /** @var \Illuminate\Filesystem\FilesystemAdapter $s3Disk */
            $s3Disk = Storage::disk('s3');
            return $s3Disk->url($path);
        }

        return rtrim(config('app.url'), '/') . '/storage/' . $path;
    }
}
