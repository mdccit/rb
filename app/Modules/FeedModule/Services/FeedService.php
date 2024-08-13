<?php

namespace App\Modules\FeedModule\Services;

use App\Models\Feed;
use App\Models\Post;
use Illuminate\Support\Facades\Validator;
use App\Extra\CommonResponse;

class FeedService
{
    /**
     * Create a new Feed and associated Post.
     *
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function createFeedAndPost(array $data)
    {
        try {
            // Validate the request data
            $validator = Validator::make($data, [
                'feed_name' => 'required|string|max:255',
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

            // Create a new Feed record using the default connection
            $feed = Feed::connect(config('database.default'))->create([
                'name' => $data['feed_name'],
            ]);

            // Create a new Post record using the feed's ID with the default connection
            $post = Post::connect(config('database.default'))->create([
                'feed_id' => $feed->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'type' => $data['type'],
            ]);

            // Return success response
            return CommonResponse::getResponse(
                200,
                $post,
                'Feed and Post created successfully'
            );

        } catch (\Exception $e) {
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went wrong'
            );
        }
    }

    /**
     * Retrieve all posts for a specific feed.
     *
     * @param Feed $feed
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllPostsForFeed(Feed $feed)
    {
        try {
            // Retrieve posts using the secondary connection
            $posts = Post::connect(config('database.secondary'))
                ->where('feed_id', $feed->id)
                ->get();

            return CommonResponse::getResponse(
                200,
                $posts,
                'Posts retrieved successfully'
            );
        } catch (\Exception $e) {
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went wrong'
            );
        }
    }

    /**
     * Retrieve a single post by its ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPostById($id)
    {
        try {
            // Retrieve the post using the secondary connection
            $post = Post::connect(config('database.secondary'))->findOrFail($id);

            return CommonResponse::getResponse(
                200,
                $post,
                'Post retrieved successfully'
            );
        } catch (\Exception $e) {
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went wrong'
            );
        }
    }

    /**
     * Update an existing post.
     *
     * @param int $id
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePost($id, array $data)
    {
        try {
            // Find the post by ID using the secondary connection
            $post = Post::connect(config('database.secondary'))->findOrFail($id);

            // Validate the request data
            $validator = Validator::make($data, [
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

            // Update the post with new data using the default connection
            $post->setConnection(config('database.default'));
            $post->update([
                'title' => $data['title'],
                'description' => $data['description'],
                'type' => $data['type'],
            ]);

            // Return the updated post as a success response
            return CommonResponse::getResponse(
                200,
                $post,
                'Post updated successfully'
            );

        } catch (\Exception $e) {
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went wrong'
            );
        }
    }

    /**
     * Delete a post by its ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deletePost($id)
    {
        try {
            // Find the post by ID using the secondary connection
            $post = Post::connect(config('database.secondary'))->findOrFail($id);

            // Delete the post using the default connection
            $post->setConnection(config('database.default'));
            $post->delete();

            return CommonResponse::getResponse(
                200,
                null,
                'Post deleted successfully'
            );
        } catch (\Exception $e) {
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went wrong'
            );
        }
    }
}
