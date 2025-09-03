<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class PostController extends BaseController
{
    public function index() {
        // Fetch posts from DB
    }

    public function show($id) {
        // Fetch single post from DB
    }

    public function store(Request $request) {
        // Create new post
    }
}
