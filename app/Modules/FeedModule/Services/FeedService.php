<?php

namespace App\Modules\FeedModule\Services;

use App\Models\Post;
use App\Models\Comment;
use App\Models\Like;
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



   /**
     * Add a comment to a post.
     *
     * @param int $postId
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function addComment($postId, array $data)
    {
        try {
            $validator = Validator::make($data, [
                'content' => 'required|string',
                'user_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors()->all(),
                    'Input validation failed'
                );
            }

            $comment = Comment::create([
                'post_id' => $postId,
                'content' => $data['content'],
                'user_id' => $data['user_id'],
            ]);

            return CommonResponse::getResponse(
                200,
                $comment,
                'Comment added successfully'
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
     * Update an existing comment.
     *
     * @param int $commentId
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateComment($commentId, array $data)
    {
        try {
            $comment = Comment::findOrFail($commentId);

            $validator = Validator::make($data, [
                'content' => 'required|string',
            ]);

            if ($validator->fails()) {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors()->all(),
                    'Input validation failed'
                );
            }

            $comment->update([
                'content' => $data['content'],
            ]);

            return CommonResponse::getResponse(
                200,
                $comment,
                'Comment updated successfully'
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
     * Delete a comment.
     *
     * @param int $commentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteComment($commentId)
    {
        try {
            $comment = Comment::findOrFail($commentId);
            $comment->delete();

            return CommonResponse::getResponse(
                200,
                null,
                'Comment deleted successfully'
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
     * Remove a like from a post.
     *
     * @param int $postId
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeLike($postId, $userId)
    {
        try {
            $like = Like::where('post_id', $postId)->where('user_id', $userId)->first();

            if ($like) {
                $like->delete();
                return CommonResponse::getResponse(
                    200,
                    null,
                    'Like removed successfully'
                );
            }

            return CommonResponse::getResponse(
                404,
                null,
                'Like not found'
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
     * Add a like to a post.
     *
     * @param int $postId
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function addLike($postId, $userId)
    {
        try {
            $like = Like::firstOrCreate([
                'post_id' => $postId,
                'user_id' => $userId,
            ]);

            return CommonResponse::getResponse(
                200,
                $like,
                'Like added successfully'
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