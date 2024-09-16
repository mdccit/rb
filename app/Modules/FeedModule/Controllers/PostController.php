<?php

namespace App\Modules\FeedModule\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\FeedModule\Services\FeedService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Extra\CommonResponse;
use App\Services\AzureBlobStorageService;

class PostController extends Controller
{
    protected $feedService;

    /**
     * PostController constructor.
     *
     * @param FeedService $feedService
     */
    public function __construct(FeedService $feedService)
    {
        $this->feedService = $feedService;
    }

    /**
     * Display a listing of the posts, optionally filtered by type.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Retrieve the query parameters from the request
        $type = $request->query('type');
        $sortBy = $request->query('sortBy', 'created_at');
        $sortOrder = $request->query('sortOrder', 'desc');

        // Call the getAllPosts method in FeedService with the retrieved parameters
        return $this->feedService->getAllPosts($type, $sortBy, $sortOrder);
    }

    /**
     * Display the specified post.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Retrieve and return a specific post by ID
        return $this->feedService->getPostById($id);
    }

     /**
     * Display the specified post.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function showSinglePost($id)
    {
        // Retrieve and return a specific post by ID
        return $this->feedService->getPostBySingle($id);
    }

    // /**
    //  * Store a newly created post.
    //  *
    //  * @param Request $request
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    // public function store(Request $request)
    // {
    //     // Create and return the newly created post
    //     return $this->feedService->createPost($request->all());
    // }


     /**
     * Store a newly created post, including image upload.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate the request for image upload
        $validator = Validator::make($request->all(), [
            'title' => [
                'required_if:type,blog,event',
                'nullable',
                'string',
                'max:255'
            ],
            'description' => 'required|string',
            'publisher_type' => 'required|in:user,school,business',
            'type' => 'required|in:post,event,blog',
            'business_id' => [
                'nullable',
                'uuid',
                'exists:businesses,id',
                'required_if:publisher_type,business',
            ],
            'has_media' => 'boolean',
            'content' => 'required|string',
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048', // Image validation
        ]);

        if ($validator->fails()) {
            // Return a validation error response
            return CommonResponse::getResponse(
                422,
                $validator->errors()->all(),
                'Input validation failed'
            );
        }
// If an image file is uploaded
if ($request->hasFile('image')) {
    // Use AzureBlobStorageService to upload the file
    $azureBlobStorageService = new AzureBlobStorageService();
    $imageUrl = $azureBlobStorageService->uploadFile($request->file('image'), 'images');

    // Add the image URL to the validated data
    $validatedData['image_url'] = $imageUrl;
}


        // Call the service to create the post
        $post = $this->feedService->createPost($request->all());

        // Return the created post and success message
        return response()->json([
            'message' => 'Post created successfully!',
            'post' => $post,
        ], 201);
    }

    /**
     * Update the specified post.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Update and return the updated post
        return $this->feedService->updatePost($id, $request->all());
    }

    /**
     * Remove the specified post from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Delete and confirm the deletion of the specified post
        return $this->feedService->deletePost($id);
    }

    /**
     * Display the specified post.
     *
     * @param int $post_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCommentsByPostId($id)
    {
        // Retrieve and return a specific post by ID
        return $this->feedService->getAllCommentsByPostId($id);
    }



    /**
     * Display the specified post.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getComment($id)
    {
        // Retrieve and return a specific post by ID
        return $this->feedService->getCommentById($id);
    }



    /**
     * Add a comment to a post.
     *
     * @param Request $request
     * @param int $postId
     * @return \Illuminate\Http\JsonResponse
     */
    public function addComment(Request $request, $postId)
    {
        return $this->feedService->addComment($postId, $request->all());
    }

    /**
     * Update a comment.
     *
     * @param Request $request
     * @param int $commentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateComment(Request $request, $commentId)
    {
        return $this->feedService->updateComment($commentId, $request->all());
    }

    /**
     * Delete a comment.
     *
     * @param int $commentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteComment($commentId)
    {
        return $this->feedService->deleteComment($commentId);
    }

    /**
     * Add a like to a post.
     *
     * @param int $postId
     * @return \Illuminate\Http\JsonResponse
     */
    public function addLike($postId)
    {
        return $this->feedService->addLike($postId);
    }

    /**
     * Remove a like from a post.
     *
     * @param int $postId
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeLike($postId, Request $request)
    {
        return $this->feedService->removeLike($postId);
    }

    /**
     * Remove a like from a post.
     *
     * @param int $postId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLoggedInUserPosts(Request $request)
    {
        // Retrieve the query parameters from the request
        $type = $request->query('type');
        $sortBy = $request->query('sortBy', 'created_at');
        $sortOrder = $request->query('sortOrder', 'desc');
        return $this->feedService->getAllPostsLoggedUser($type, $sortBy, $sortOrder);
    }
}