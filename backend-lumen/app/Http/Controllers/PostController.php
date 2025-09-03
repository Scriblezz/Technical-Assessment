<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends BaseController
{
    public function __construct()
    {
        // No-op
    }

    public function index()
    {
    $posts = Post::query()->orderBy('id')->get(['id', 'title', 'content', 'user_id', 'created_at']);
    return response()->json($posts);
    }

    public function show($id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }
        return response()->json($post);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'content' => 'required',
        ]);

        $post = Post::create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'user_id' => $request->input('user_id', 1),
        ]);

        return response()->json($post, 201);
    }
}
