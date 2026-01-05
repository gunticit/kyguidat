<?php

namespace App\Http\Controllers;

use App\Traits\HasFileUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    use HasFileUpload;

    /**
     * Upload a single file
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:' . ($this->getMaxUploadSize() / 1024),
            'directory' => 'nullable|string|max:100',
        ]);

        try {
            $directory = $request->input('directory', 'uploads');
            $result = $this->uploadFile($request->file('file'), $directory);

            return response()->json([
                'success' => true,
                'message' => 'Upload thành công',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload multiple files
     */
    public function uploadMultiple(Request $request): JsonResponse
    {
        $request->validate([
            'files' => 'required|array|max:20',
            'files.*' => 'file|max:' . ($this->getMaxUploadSize() / 1024),
            'directory' => 'nullable|string|max:100',
        ]);

        try {
            $directory = $request->input('directory', 'uploads');
            $results = $this->uploadMultipleFiles($request->file('files'), $directory);

            return response()->json([
                'success' => true,
                'message' => 'Upload thành công ' . count($results) . ' file',
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload an image with optional resizing
     */
    public function uploadImageHandler(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|max:' . ($this->getMaxUploadSize() / 1024),
            'directory' => 'nullable|string|max:100',
            'width' => 'nullable|integer|min:50|max:4000',
            'height' => 'nullable|integer|min:50|max:4000',
            'quality' => 'nullable|integer|min:10|max:100',
        ]);

        try {
            $directory = $request->input('directory', 'images');
            $options = array_filter([
                'width' => $request->input('width'),
                'height' => $request->input('height'),
                'quality' => $request->input('quality'),
            ]);

            // Call trait method explicitly
            $image = $request->file('image');
            $result = $this->uploadImage($image, $directory, $options);

            return response()->json([
                'success' => true,
                'message' => 'Upload ảnh thành công',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload ảnh thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload from base64
     */
    public function uploadBase64(Request $request): JsonResponse
    {
        $request->validate([
            'base64' => 'required|string',
            'directory' => 'nullable|string|max:100',
            'extension' => 'nullable|string|in:jpg,jpeg,png,gif,webp',
        ]);

        try {
            $directory = $request->input('directory', 'uploads');
            $extension = $request->input('extension');
            
            $result = $this->uploadFromBase64($request->input('base64'), $directory, $extension);

            return response()->json([
                'success' => true,
                'message' => 'Upload thành công',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a file
     */
    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string',
            'disk' => 'nullable|string|in:public,s3',
        ]);

        try {
            $deleted = $this->deleteFile($request->input('path'), $request->input('disk'));

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Xóa file thành công',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy file',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Xóa file thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get storage info
     */
    public function info(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'storage' => $this->getStorageStats(),
                'max_upload_size' => $this->getMaxUploadSize(),
                'max_upload_size_mb' => round($this->getMaxUploadSize() / 1024 / 1024, 2),
                'allowed_extensions' => config('filesystems.allowed_extensions'),
            ],
        ]);
    }
}
