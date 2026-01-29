<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserPostController extends Controller
{
    public function index(Request $request)
    {
        $posts = $request->user()
            ->posts()
            ->with('author:id,name')
            ->latest()
            ->paginate(10);

        return response()->json($posts);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePost($request);

        $post = new Post($validated);
        $post->user_id = $request->user()->id;
        $post->slug = $this->makeUniqueSlug($validated['title']);

        if ($request->hasFile('image')) {
            $post->image_path = $request->file('image')->store('posts', 'public');
        }

        $post->save();

        return response()->json($post->load('author:id,name'), 201);
    }

    public function update(Request $request, Post $post)
    {
        if ($post->user_id !== $request->user()->id) {
            return response()->json(['message' => 'You do not own this post.'], 403);
        }

        $validated = $this->validatePost($request, true);

        if (array_key_exists('title', $validated) && $validated['title'] !== $post->title) {
            $post->slug = $this->makeUniqueSlug($validated['title'], $post);
        }

        $post->fill($validated);

        if ($request->hasFile('image')) {
            if ($post->image_path) {
                Storage::disk('public')->delete($post->image_path);
            }

            $post->image_path = $request->file('image')->store('posts', 'public');
        }

        $post->save();

        return response()->json($post->load('author:id,name'));
    }

    public function destroy(Request $request, Post $post)
    {
        if ($post->user_id !== $request->user()->id) {
            return response()->json(['message' => 'You do not own this post.'], 403);
        }

        if ($post->image_path) {
            Storage::disk('public')->delete($post->image_path);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted.']);
    }

    private function validatePost(Request $request, bool $isUpdate = false): array
    {
        $required = $isUpdate ? 'sometimes' : 'required';

        return $request->validate([
            'title' => [$required, 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'body' => [$required, 'string'],
            'published_at' => ['nullable', 'date'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);
    }

    private function makeUniqueSlug(string $title, ?Post $post = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $suffix = 1;

        while (
            Post::where('slug', $slug)
                ->when($post, fn ($query) => $query->where('id', '!=', $post->id))
                ->exists()
        ) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
