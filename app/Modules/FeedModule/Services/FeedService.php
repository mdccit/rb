<?php

namespace App\Modules\FeedModule\Services;

use App\Models\Post;
use App\Models\Comment;
use App\Models\Like;
use Illuminate\Support\Facades\Validator;
use App\Extra\CommonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\SchoolUser;

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
                'title' => [
                    'required_if:type,blog,event',
                    'nullable',
                    'string',
                    'max:255'
                ],
                'description' => 'required|string',
                'publisher_type' => 'required|in:user,school,business',
                'type' => 'required|in:post,event,blog',
                // 'school_id' => [
                //     'nullable',
                //     'uuid',
                //     'exists:schools,id',
                //     'required_if:publisher_type,school',
                // ],
                'business_id' => [
                    'nullable',
                    'uuid',
                    'exists:businesses,id',
                    'required_if:publisher_type,business',
                ],
                'has_media' => 'boolean',
            ]);

            

            if ($validator->fails()) {
                // Return a validation error response
                return CommonResponse::getResponse(
                    422,
                    $validator->errors()->all(),
                    'Input validation failed'
                );
            }

            if(Auth::user()->user_role_id==5){
                $school_User = SchoolUser::connect(config('database.secondary'))->where('user_id','=',auth()->id())->first();
                $dataToInsert = [
                    'user_id' => Auth::id(),
                    'school_id' =>  $school_User->school_id,
                    'business_id' => $data['business_id'] ?? null,
                    'publisher_type' => $data['publisher_type'],
                    'has_media' => $data['has_media'] ?? false,
                    'type' => $data['type'],
                    'seo_url' =>  Str::random(8),
                    'description' => $data['description'],
                ];
                // Conditionally add the title if the type is blog or event
                if (in_array($data['type'], ['blog', 'event'])) {
                    $dataToInsert['title'] = $data['title'];
                }
    
    
                // Create a new Post record using the default database connection
                $post = Post::connect(config('database.default'))->create($dataToInsert);
    
                // Generate the SEO URL based on the type
                if ($data['type'] === 'post') {
                    $seoUrl = $post->id; // Use the post ID as the SEO URL
                } else {
                    // Generate a slug from the title
                    $baseSeoUrl = Str::slug($data['title']);
                    $seoUrl = $baseSeoUrl;
    
                    // Check if the SEO URL already exists in the posts table
                    $existingSeoUrlCount = Post::where('seo_url', 'like', "$baseSeoUrl%")->count();
    
                    if ($existingSeoUrlCount > 0) {
                        // If it exists, append a unique suffix
                        $seoUrl = "{$baseSeoUrl}-" . ($existingSeoUrlCount + 1);
                    }
                }
    
                // Ensure the SEO URL is unique (handle potential race conditions)
                while (Post::where('seo_url', $seoUrl)->exists()) {
                    $seoUrl .= '-' . Str::random(8); // Add a random suffix to ensure uniqueness
                }
    
                // Update the post with the generated SEO URL
                $post->update(['seo_url' => $seoUrl]);
    
                // Return a success response with the created post
                return CommonResponse::getResponse(
                    200,
                    $post,
                    'Post created successfully'
                );
            }else{
                return CommonResponse::getResponse(
                    422,
                    "Only Coaches can create post",
                    'Invalid User'
                );
            }
           

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
            $post = Post::connect(config('database.secondary'))
                     ->withCount('likes')
                     ->withCount('comments')
                     ->with([
                        'comments' => function ($query) {
                            $query->with('user'); // Eager load the user relationship for each comment
                        }
                    ])
                     ->with('likes')
                     ->with('school')
                     ->with('business')
                     ->with('user')
                     ->findOrFail($id);

            
                
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
    public function getPostBySingle($id)
    {
        try {
            $userId = auth()->id();
            // Find the post by ID using the secondary database connection
            $post = Post::connect(config('database.secondary'))
                     ->withCount('likes')
                     ->withCount('comments')
                     ->with([
                        'comments' => function ($query) {
                            $query->with('user')
                                  ->orderBy('created_at', 'DESC');  // Eager load the user relationship for each comment
                        }
                    ])
                     ->with('likes')
                     ->with('school')
                     ->with('business')
                     ->with('user')
                     ->findOrFail($id);

            $post->user_has_liked = $post->likes->contains('user_id', $userId);
                
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
     * Retrieve all comments for a specific post by its ID.
     *
     * @param string $postId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllCommentsByPostId($postId)
    {
        try {
            // Find all comments for the given post ID using the secondary database connection
            $comments = Comment::on(config('database.secondary'))
                ->where('post_id', $postId)
                ->get();

            // Return a success response with the retrieved comments
            return CommonResponse::getResponse(
                200,
                $comments,
                'Comments retrieved successfully'
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
     * Retrieve a single comment by its ID.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCommentById($id)
    {
        try {
            // Find the comment by ID using the secondary database connection
            $comment = Comment::on(config('database.secondary'))->findOrFail($id);

            // Return a success response with the retrieved comment
            return CommonResponse::getResponse(
                200,
                $comment,
                'Comment retrieved successfully'
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
     * Remove a like from a post.
     *
     * @param int $postId
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
                    'Like removed successfully',
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function addLike($postId)
    {

        try {
            $like = Like::firstOrCreate([
                'post_id' => $postId,
                'user_id' => Auth::id(),
            ]);

            return CommonResponse::getResponse(
                200,
                'Like added successfully',
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


    public function getAllPostsLoggedUser($type = null, $sortBy = 'created_at', $sortOrder = 'desc')
    {
        try {
            $userId = Auth::id();

            $query = Post::connect(config('database.secondary'))
                ->withCount('likes')
                ->withCount('comments')
                ->with([
                    'comments' => function ($query) {
                        $query->with('user')
                              ->orderBy('created_at', 'DESC');  // Eager load the user relationship for each comment
                    }
                ])
                ->with([
                    'likes' => function ($query) use ($userId) {
                        $query->where('user_id', $userId);
                    }
                ])
                ->with('school')
                ->with('business')
                ->with('user');

            // If a type is provided, filter the posts by the specified type
            if ($type) {
                $query->where('type', $type);
            }

            // Sort posts by the specified sort column and order
            $query->orderBy($sortBy, $sortOrder);

            // Execute the query and get the results
            $posts = $query->get()->map(function ($post) use ($userId) {
                // Add the user's like status to each post
                $post->user_has_liked = $post->likes->contains('user_id', $userId);
                // Remove the likes relationship as we only needed it for checking the user's like status
                unset($post->likes);
                return $post;
            });

            // Return a success response with the retrieved posts and their interactions
            return CommonResponse::getResponse(
                200,
                $posts,
                'Posts for loggedin user retrieved successfully'
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
