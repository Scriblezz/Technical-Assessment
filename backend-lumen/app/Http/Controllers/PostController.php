<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class PostController extends BaseController
{
    private $storagePath;

    public function __construct()
    {
        // storage file placed at project_root/storage/app/posts.json
        $this->storagePath = dirname(__DIR__, 3) . '/storage/app/posts.json';

        // ensure storage dir exists
        $dir = dirname($this->storagePath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        // initialize file if missing
        if (!file_exists($this->storagePath)) {
            file_put_contents($this->storagePath, json_encode([
                ['id' => 1, 'title' => 'Welcome', 'content' => 'This is the first post.']
            ], JSON_PRETTY_PRINT));
        }
    }

    protected function readPosts()
    {
        $json = @file_get_contents($this->storagePath);
        $data = json_decode($json, true);
        if (!is_array($data)) {
            return [];
        }
        return $data;
    }

    protected function writePosts(array $posts)
    {
        file_put_contents($this->storagePath, json_encode(array_values($posts), JSON_PRETTY_PRINT));
    }

    public function index()
    {
        $posts = $this->readPosts();
        return response()->json($posts);
    }

    public function show($id)
    {
        $posts = $this->readPosts();
        foreach ($posts as $p) {
            if ((int)$p['id'] === (int)$id) {
                return response()->json($p);
            }
        }
        return response()->json(['message' => 'Post not found'], 404);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'content' => 'required',
        ]);

        $posts = $this->readPosts();
        $maxId = 0;
        foreach ($posts as $p) {
            $maxId = max($maxId, (int)$p['id']);
        }

        $new = [
            'id' => $maxId + 1,
            'title' => $request->input('title'),
            'content' => $request->input('content'),
        ];

        $posts[] = $new;
        $this->writePosts($posts);

        return response()->json($new, 201);
    }
}
