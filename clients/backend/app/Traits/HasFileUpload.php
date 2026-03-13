<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasFileUpload
{
    /**
     * Get the active storage disk based on configuration
     * Returns 's3' if configured, otherwise 'public'
     */
    protected function getStorageDisk(): string
    {
        // Check if S3 is configured
        if ($this->isS3Configured()) {
            return 's3';
        }

        return 'public';
    }

    /**
     * Check if S3 is properly configured
     */
    protected function isS3Configured(): bool
    {
        return !empty(config('filesystems.disks.s3.key')) 
            && !empty(config('filesystems.disks.s3.secret')) 
            && !empty(config('filesystems.disks.s3.bucket'));
    }

    /**
     * Upload a single file
     * 
     * @param UploadedFile $file
     * @param string $directory Directory to store the file
     * @param string|null $filename Custom filename (optional)
     * @return array{path: string, url: string, disk: string}
     */
    public function uploadFile(UploadedFile $file, string $directory = 'uploads', ?string $filename = null): array
    {
        $disk = $this->getStorageDisk();
        
        // Generate filename if not provided
        if (!$filename) {
            $filename = $this->generateFilename($file);
        }

        $path = $file->storeAs($directory, $filename, $disk);
        
        return [
            'path' => $path,
            'url' => $this->getFileUrl($path, $disk),
            'disk' => $disk,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ];
    }

    /**
     * Upload multiple files
     * 
     * @param array $files Array of UploadedFile
     * @param string $directory Directory to store files
     * @return array
     */
    public function uploadMultipleFiles(array $files, string $directory = 'uploads'): array
    {
        $results = [];
        
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $results[] = $this->uploadFile($file, $directory);
            }
        }
        
        return $results;
    }

    /**
     * Upload an image with optional resizing
     * 
     * @param UploadedFile $image
     * @param string $directory
     * @param array $options Resize options (width, height, quality)
     * @return array
     */
    public function uploadImage(UploadedFile $image, string $directory = 'images', array $options = []): array
    {
        // Validate image
        if (!$this->isValidImage($image)) {
            throw new \InvalidArgumentException('File không phải là hình ảnh hợp lệ');
        }

        $disk = $this->getStorageDisk();
        $filename = $this->generateFilename($image);

        // If intervention/image is installed and resize options are provided
        if (class_exists('\Intervention\Image\ImageManager') && !empty($options)) {
            return $this->uploadResizedImage($image, $directory, $filename, $disk, $options);
        }

        // Normal upload
        $path = $image->storeAs($directory, $filename, $disk);
        
        return [
            'path' => $path,
            'url' => $this->getFileUrl($path, $disk),
            'disk' => $disk,
            'original_name' => $image->getClientOriginalName(),
            'size' => $image->getSize(),
            'mime_type' => $image->getMimeType(),
        ];
    }

    /**
     * Upload resized image using Intervention Image
     */
    protected function uploadResizedImage(
        UploadedFile $image, 
        string $directory, 
        string $filename, 
        string $disk,
        array $options
    ): array {
        /** @phpstan-ignore-next-line - Intervention Image is optional dependency */
        $manager = new \Intervention\Image\ImageManager(['driver' => 'gd']);
        $img = $manager->make($image->getRealPath());

        // Resize if dimensions provided
        if (isset($options['width']) || isset($options['height'])) {
            $img->resize(
                $options['width'] ?? null,
                $options['height'] ?? null,
                function ($constraint) use ($options) {
                    $constraint->aspectRatio();
                    if ($options['upsize'] ?? true) {
                        $constraint->upsize();
                    }
                }
            );
        }

        // Get image data
        $quality = $options['quality'] ?? 90;
        $imageData = $img->encode($image->extension(), $quality);

        // Store the resized image
        $path = $directory . '/' . $filename;
        Storage::disk($disk)->put($path, $imageData);

        return [
            'path' => $path,
            'url' => $this->getFileUrl($path, $disk),
            'disk' => $disk,
            'original_name' => $image->getClientOriginalName(),
            'size' => strlen($imageData),
            'mime_type' => $image->getMimeType(),
            'width' => $img->width(),
            'height' => $img->height(),
        ];
    }

    /**
     * Delete a file from storage
     * 
     * @param string $path File path
     * @param string|null $disk Storage disk (auto-detect if null)
     * @return bool
     */
    public function deleteFile(string $path, ?string $disk = null): bool
    {
        $disk = $disk ?? $this->getStorageDisk();
        
        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }

    /**
     * Delete multiple files
     * 
     * @param array $paths Array of file paths
     * @param string|null $disk
     * @return int Number of files deleted
     */
    public function deleteMultipleFiles(array $paths, ?string $disk = null): int
    {
        $deleted = 0;
        
        foreach ($paths as $path) {
            if ($this->deleteFile($path, $disk)) {
                $deleted++;
            }
        }
        
        return $deleted;
    }

    /**
     * Get the full URL of a file
     * 
     * @param string $path
     * @param string|null $disk
     * @return string
     */
    public function getFileUrl(string $path, ?string $disk = null): string
    {
        $disk = $disk ?? $this->getStorageDisk();
        
        // For S3, use the URL method
        if ($disk === 's3') {
            /** @var \Illuminate\Filesystem\FilesystemAdapter $s3Disk */
            $s3Disk = Storage::disk('s3');
            return $s3Disk->url($path);
        }

        // For local/public disk, use asset URL
        return rtrim(config('app.url'), '/') . '/storage/' . $path;
    }

    /**
     * Check if a file exists
     * 
     * @param string $path
     * @param string|null $disk
     * @return bool
     */
    public function fileExists(string $path, ?string $disk = null): bool
    {
        $disk = $disk ?? $this->getStorageDisk();
        return Storage::disk($disk)->exists($path);
    }

    /**
     * Generate a unique filename
     * 
     * @param UploadedFile $file
     * @return string
     */
    protected function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Ymd_His');
        $random = Str::random(8);
        
        return "{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Validate if file is an image
     * 
     * @param UploadedFile $file
     * @return bool
     */
    protected function isValidImage(UploadedFile $file): bool
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        return in_array($file->getMimeType(), $allowedMimes);
    }

    /**
     * Get maximum upload size in bytes
     */
    protected function getMaxUploadSize(): int
    {
        return config('filesystems.max_upload_size', 10 * 1024 * 1024); // Default 10MB
    }

    /**
     * Move a file from one location to another
     */
    public function moveFile(string $from, string $to, ?string $disk = null): bool
    {
        $disk = $disk ?? $this->getStorageDisk();
        return Storage::disk($disk)->move($from, $to);
    }

    /**
     * Copy a file
     */
    public function copyFile(string $from, string $to, ?string $disk = null): bool
    {
        $disk = $disk ?? $this->getStorageDisk();
        return Storage::disk($disk)->copy($from, $to);
    }

    /**
     * Get file contents
     */
    public function getFileContents(string $path, ?string $disk = null): ?string
    {
        $disk = $disk ?? $this->getStorageDisk();
        
        if ($this->fileExists($path, $disk)) {
            return Storage::disk($disk)->get($path);
        }
        
        return null;
    }

    /**
     * Upload from base64 string
     */
    public function uploadFromBase64(string $base64, string $directory = 'uploads', ?string $extension = null): array
    {
        $disk = $this->getStorageDisk();
        
        // Decode base64
        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
            $extension = $extension ?? $matches[1];
            $base64 = substr($base64, strpos($base64, ',') + 1);
        }
        
        $extension = $extension ?? 'png';
        $data = base64_decode($base64);
        
        if ($data === false) {
            throw new \InvalidArgumentException('Invalid base64 data');
        }

        $filename = now()->format('Ymd_His') . '_' . Str::random(8) . '.' . $extension;
        $path = $directory . '/' . $filename;
        
        Storage::disk($disk)->put($path, $data);
        
        return [
            'path' => $path,
            'url' => $this->getFileUrl($path, $disk),
            'disk' => $disk,
            'size' => strlen($data),
        ];
    }

    /**
     * Get storage usage statistics
     */
    public function getStorageStats(?string $disk = null): array
    {
        $disk = $disk ?? $this->getStorageDisk();
        
        return [
            'disk' => $disk,
            'is_s3' => $disk === 's3',
            's3_configured' => $this->isS3Configured(),
        ];
    }
}
