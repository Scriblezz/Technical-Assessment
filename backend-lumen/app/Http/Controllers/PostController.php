<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;

class PostController extends BaseController
{
    public function __construct()
    {
        // No-op
    }

    public function index()
    {
        $posts = Cache::remember('posts', 600, function () {
            return Post::query()->orderBy('id')->get(['id', 'title', 'content', 'user_id', 'created_at']);
        });
        return response()->json($posts);
    }

    public function show($id)
    {
        $post = Cache::remember("post:$id", 600, function () use ($id) {
            return Post::find($id);
        });
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }
        return response()->json($post);
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'title' => 'required',
                'content' => 'required',
            ]);

            $post = Post::create([
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'user_id' => $request->input('user_id', 1),
            ]);

            // Invalidate caches
            Cache::forget('posts');
            Cache::forget("post:{$post->id}");

            return response()->json($post, 201);
        } catch (\Throwable $e) {
            \Log::error('POST /api/posts failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Failed to create post', 'message' => $e->getMessage()], 500);
        }
    }
}
