<?php

namespace App\Modules\FeedModule\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Feed;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Extra\CommonResponse;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
  public function index(Feed $feed)
  {
    $posts = $feed->posts;
    return response()->json($posts);
  }

  public function create(Feed $feed)
  {
    return view('posts.create', compact('feed'));
  }

  public function store(Request $request, Feed $feed)
  {


    try {
      // Validate the request data
      $validator = Validator::make($request->all(), [
        'feed_name' => 'required|string|max:255', // Assuming each feed has a name
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'type' => 'required|in:blog,event,status',
      ]);

      if ($validator->fails()) {
        return CommonResponse::getResponse(
          422,
          $validator->errors()->all(),
          'Input validation failed'
        );
      }


      // Create a new Feed record
      $feed = Feed::create([
        'name' => $request->feed_name,
      ]);

      // Create a new Post record using the feed's ID
      Post::create([
        'feed_id' => $feed->id,
        'title' => $request->title,
        'description' => $request->description,
        'type' => $request->type,
      ]);


      return CommonResponse::getResponse(
        200,
        'Successfully Created',
        'Successfully Created'
      );
    } catch (\Exception $e) {
      return CommonResponse::getResponse(
        422,
        $e->getMessage(),
        'Something went to wrong'
      );
    }

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
