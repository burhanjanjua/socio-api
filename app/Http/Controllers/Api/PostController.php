<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::published()
            ->with('author:id,name')
            ->latest('published_at')
            ->paginate(10);

        return response()->json($posts);
    }

    public function show(Post $post)
    {
        $user = auth('sanctum')->user();

        if (! $post->is_published) {
            if (! $user || (! $user->isAdmin() && $post->user_id !== $user->id)) {
                return response()->json(['message' => 'Post not found.'], 404);
            }
        }

        return response()->json($post->load('author:id,name'));
    }
}
