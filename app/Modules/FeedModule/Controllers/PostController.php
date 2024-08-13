<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Feed $feed)
    {
        $posts = $feed->posts;
        return view('posts.index', compact('feed', 'posts'));
    }

    public function create(Feed $feed)
    {
        return view('posts.create', compact('feed'));
    }

    public function store(Request $request, Feed $feed)
    {
        $request->validate([
            'type' => 'required|in:blog,event,status',
            'title' => 'required_if:type,blog,event|string|max:255',
            'description' => 'required|string',
        ]);

        $feed->posts()->create([
            'type' => $request->type,
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return redirect()->route('feeds.posts.index', $feed);
    }

    public function edit(Feed $feed, Post $post)
    {
        return view('posts.edit', compact('feed', 'post'));
    }

    public function update(Request $request, Feed $feed, Post $post)
    {
        $request->validate([
            'type' => 'required|in:blog,event,status',
            'title' => 'required_if:type,blog,event|string|max:255',
            'description' => 'required|string',
        ]);

        $post->update([
            'type' => $request->type,
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return redirect()->route('feeds.posts.index', $feed);
    }

    public function destroy(Feed $feed, Post $post)
    {
        $post->delete();
        return redirect()->route('feeds.posts.index', $feed);
    }
}
