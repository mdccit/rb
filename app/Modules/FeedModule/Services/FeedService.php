<?php

namespace App\Modules\FeedModule\Services;

use App\Models\Post;
use App\Models\Comment;
use App\Models\Like;
use Illuminate\Support\Facades\Validator;
use App\Extra\CommonResponse;
use Illuminate\Support\Facades\Auth;


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

        // dd(Auth::id());
        try {
            // Validate the incoming request data
            $validator = Validator::make($data, [
                'title' => [
                    'required_if:type,blog,event', 
                    'nullable',                 
                    'string',
                    'max:255'
                ],
                'description' => 'required|string',
                'publisher_type' => 'required|in:user,school,business',
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


            $dataToInsert = [
                'user_id' => Auth::id(),
                'description' => $data['description'],
                'type' => $data['type'],
            ];
            
            // Conditionally add the title if the type is blog or event
            if (in_array($data['type'], ['blog', 'event'])) {
                $dataToInsert['title'] = $data['title'];
            }
            

            // Create a new Post record using the default database connection
            $post = Post::connect(config('database.default'))->create($dataToInsert);

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
    public function getAllPosts($type = null, $sortBy = 'created_at', $sortOrder = 'desc')
    {
        try {
            // Build the query using the secondary database connection
            $query = Post::connect(config('database.secondary'));
    
            // If a type is provided, filter the posts by the specified type
            if ($type) {
                $query->where('type', $type);
            }
    
            // Sort posts by the specified sort column and order
            $query->orderBy($sortBy, $sortOrder);
    
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
                'user_id' => Auth::id(),
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
    public function removeLike($postId)
    {
        try {
            $like = Like::where('post_id', $postId)->where('user_id', Auth::id())->first();

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
                'user_id' => Auth::id(),
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
