<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminPostController extends Controller
{
    public function index()
    {
        $posts = Post::with('author:id,name')
            ->latest()
            ->paginate(15);

        return response()->json($posts);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePost($request);
        $authorId = $validated['author_id'] ?? null;
        unset($validated['author_id']);

        $post = new Post($validated);
        $post->user_id = $authorId ?? $request->user()->id;
        $post->slug = $this->makeUniqueSlug($validated['title']);

        if ($request->hasFile('image')) {
            $post->image_path = $request->file('image')->store('posts', 'public');
        }

        $post->save();

        return response()->json($post->load('author:id,name'), 201);
    }

    public function update(Request $request, Post $post)
    {
        $validated = $this->validatePost($request, true);
        $authorId = $validated['author_id'] ?? null;
        unset($validated['author_id']);

        if (array_key_exists('title', $validated) && $validated['title'] !== $post->title) {
            $post->slug = $this->makeUniqueSlug($validated['title'], $post);
        }

        if ($authorId !== null) {
            $post->user_id = $authorId;
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

    public function destroy(Post $post)
    {
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
            'author_id' => ['nullable', 'integer', 'exists:users,id'],
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
