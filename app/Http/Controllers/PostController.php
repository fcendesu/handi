<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
        ]);

        $post = auth()->user()->posts()->create([
            'title' => $request->title,
            'content' => $request->content,
        ]);

        return response()->json([
            'message' => 'Post created successfully',
            'post' => $post
        ], 201);
    }
}
