<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Article;
use App\Models\Consignment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    /**
     * List all pages
     */
    public function index(Request $request)
    {
        $query = Page::orderBy('display_order', 'asc')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pages = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $pages->items(),
            'total' => $pages->total(),
            'current_page' => $pages->currentPage(),
            'last_page' => $pages->lastPage(),
        ]);
    }

    /**
     * Show single page
     */
    public function show($id)
    {
        $page = Page::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $page,
        ]);
    }

    /**
     * Create new page
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:pages,slug',
                'content' => 'required|string',
                'status' => 'in:draft,published',
                'display_order' => 'nullable|integer',
            ]);

            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['title']);
                $original = $validated['slug'];
                $count = 1;
                while (
                    Page::where('slug', $validated['slug'])->exists()
                    || Article::where('slug', $validated['slug'])->exists()
                    || Consignment::where('seo_url', $validated['slug'])->exists()
                ) {
                    $validated['slug'] = $original . '-' . $count;
                    $count++;
                }
            } else {
                // Check cross-table uniqueness
                if (Article::where('slug', $validated['slug'])->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Slug đã được sử dụng bởi một bài viết',
                        'errors' => ['slug' => ['Slug đã được sử dụng bởi một bài viết']],
                    ], 422);
                }
                if (Consignment::where('seo_url', $validated['slug'])->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Slug đã được sử dụng bởi một sản phẩm',
                        'errors' => ['slug' => ['Slug đã được sử dụng bởi một sản phẩm']],
                    ], 422);
                }
            }

            $page = Page::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Tạo trang thành công',
                'data' => $page,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Update page
     */
    public function update(Request $request, $id)
    {
        $page = Page::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:pages,slug,' . $id,
            'content' => 'sometimes|string',
            'status' => 'in:draft,published',
            'display_order' => 'nullable|integer',
        ]);

        // Cross-table check
        if (!empty($validated['slug'])) {
            if (Article::where('slug', $validated['slug'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Slug đã được sử dụng bởi một bài viết',
                    'errors' => ['slug' => ['Slug đã được sử dụng bởi một bài viết']],
                ], 422);
            }
            if (Consignment::where('seo_url', $validated['slug'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Slug đã được sử dụng bởi một sản phẩm',
                    'errors' => ['slug' => ['Slug đã được sử dụng bởi một sản phẩm']],
                ], 422);
            }
        }

        $page->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trang thành công',
            'data' => $page->fresh(),
        ]);
    }

    /**
     * Delete page
     */
    public function destroy($id)
    {
        $page = Page::findOrFail($id);
        $page->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa trang thành công',
        ]);
    }

    /**
     * Public: show page by slug
     */
    public function publicShow($slug)
    {
        $page = Page::published()
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $page,
        ]);
    }
}
