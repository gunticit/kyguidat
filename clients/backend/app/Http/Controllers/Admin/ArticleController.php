<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    /**
     * List all articles (with search & filter)
     */
    public function index(Request $request)
    {
        $query = Article::with('author:id,name')
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $articles = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $articles->items(),
            'total' => $articles->total(),
            'current_page' => $articles->currentPage(),
            'last_page' => $articles->lastPage(),
        ]);
    }

    /**
     * Show single article
     */
    public function show($id)
    {
        $article = Article::with('author:id,name')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $article,
        ]);
    }

    /**
     * Create new article
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:articles,slug',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|string',
            'status' => 'in:draft,published',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
            $count = Article::where('slug', 'like', $validated['slug'] . '%')->count();
            if ($count > 0) {
                $validated['slug'] .= '-' . ($count + 1);
            }
        }

        $validated['author_id'] = $request->user()->id;

        if (($validated['status'] ?? '') === 'published') {
            $validated['published_at'] = now();
        }

        $article = Article::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tạo bài viết thành công',
            'data' => $article->load('author:id,name'),
        ], 201);
    }

    /**
     * Update article
     */
    public function update(Request $request, $id)
    {
        $article = Article::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:articles,slug,' . $id,
            'excerpt' => 'nullable|string|max:500',
            'content' => 'sometimes|string',
            'featured_image' => 'nullable|string',
            'status' => 'in:draft,published',
        ]);

        // Set published_at when publishing for the first time
        if (($validated['status'] ?? '') === 'published' && !$article->published_at) {
            $validated['published_at'] = now();
        }

        $article->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật bài viết thành công',
            'data' => $article->fresh()->load('author:id,name'),
        ]);
    }

    /**
     * Delete article
     */
    public function destroy($id)
    {
        $article = Article::findOrFail($id);
        $article->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa bài viết thành công',
        ]);
    }

    /**
     * Public: list published articles
     */
    public function publicIndex(Request $request)
    {
        $articles = Article::published()
            ->select('id', 'title', 'slug', 'excerpt', 'featured_image', 'published_at')
            ->orderBy('published_at', 'desc')
            ->paginate($request->input('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $articles->items(),
            'total' => $articles->total(),
        ]);
    }

    /**
     * Public: show single published article by slug
     */
    public function publicShow($slug)
    {
        $article = Article::published()
            ->with('author:id,name')
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $article,
        ]);
    }
}
