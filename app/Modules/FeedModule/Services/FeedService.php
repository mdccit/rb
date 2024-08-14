<?php

namespace App\Modules\FeedModule\Services;

use App\Models\Post;
use Illuminate\Support\Facades\Validator;
use App\Extra\CommonResponse;

class FeedService
{
    /**
     * Create a new post with a given type (post, event, blog).
     *
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPost(array $data)
    {
        try {
            // Validate the incoming request data
            $validator = Validator::make($data, [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'type' => 'required|in:post,event,blog',
            ]);

            if ($validator->fails()) {
                // Return a validation error response
                return CommonResponse::getResponse(
                    422,
                    $validator->errors()->all(),
                    'Input validation failed'
                );
            }

            // Create a new Post record using the default database connection
            $post = Post::connect(config('database.default'))->create([
                'title' => $data['title'],
                'description' => $data['description'],
                'type' => $data['type'],
            ]);

            // Return a success response with the created post
            return CommonResponse::getResponse(
                200,
                $post,
                'Post created successfully'
            );

        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went wrong'
            );
        }
    }

    /**
     * Retrieve all posts, optionally filtered by type.
     *
     * @param string|null $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllPosts($type = null)
    {
        try {
            // Build the query using the secondary database connection
            $query = Post::connect(config('database.secondary'));

            // If a type is provided, filter the posts by the specified type
            if ($type) {
                $query->where('type', $type);
            }

            // Execute the query and get the results
            $posts = $query->get();

            // Return a success response with the retrieved posts
            return CommonResponse::getResponse(
                200,
                $posts,
                'Posts retrieved successfully'
            );
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
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
            // Find the post by ID using the secondary database connection
            $post = Post::connect(config('database.secondary'))->findOrFail($id);

            // Return a success response with the retrieved post
            return CommonResponse::getResponse(
                200,
                $post,
                'Post retrieved successfully'
            );
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went wrong'
            );
        }
    }

    /**
     * Update an existing post by its ID.
     *
     * @param int $id
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePost($id, array $data)
    {
        try {
            // Find the post by ID using the secondary database connection
            $post = Post::connect(config('database.secondary'))->findOrFail($id);

            // Validate the incoming request data
            $validator = Validator::make($data, [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'type' => 'required|in:post,event,blog',
            ]);

            if ($validator->fails()) {
                // Return a validation error response
                return CommonResponse::getResponse(
                    422,
                    $validator->errors()->all(),
                    'Input validation failed'
                );
            }

            // Update the post using the default database connection
            $post->setConnection(config('database.default'));
            $post->update([
                'title' => $data['title'],
                'description' => $data['description'],
                'type' => $data['type'],
            ]);

            // Return a success response with the updated post
            return CommonResponse::getResponse(
                200,
                $post,
                'Post updated successfully'
            );

        } catch (\Exception $e) {
            // Return an error response if something goes wrong
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
            // Find the post by ID using the secondary database connection
            $post = Post::connect(config('database.secondary'))->findOrFail($id);

            // Delete the post using the default database connection
            $post->setConnection(config('database.default'));
            $post->delete();

            // Return a success response confirming the deletion
            return CommonResponse::getResponse(
                200,
                null,
                'Post deleted successfully'
            );
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went wrong'
            );
        }
    }
}
