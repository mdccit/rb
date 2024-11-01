<?php

namespace App\Modules\FeedModule\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\FeedModule\Services\FeedService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Extra\CommonResponse;
use App\Services\AzureBlobStorageService;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    protected $feedService;
    protected $azureBlobStorageService;


    /**
     * PostController constructor.
     *
     * @param FeedService $feedService
     */
    public function __construct(FeedService $feedService, AzureBlobStorageService $azureBlobStorageService)
    {
        $this->feedService = $feedService;
        $this->azureBlobStorageService = $azureBlobStorageService;
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
        // Validate the request for image and video uploads
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
            'media_files' => 'nullable', 
            'media_files.*' => 'required|mimes:jpg,jpeg,png,mp4|max:51200',
        ]);

        if ($validator->fails()) {
            return CommonResponse::getResponse(422, $validator->errors()->all(), 'Input validation failed');
        }
        // Set `has_media` based on the presence of media files in the request
        $hasMedia = $request->hasFile('media_files') ? 1 : 0;
        Log::info('Has media: ' . $hasMedia); // Debugging log

        // Include `has_media` in the request data
        $postData = array_merge($request->all(), ['has_media' => $hasMedia]);


        // Call the createPost method, which returns a JSON response
        $postResponse = $this->feedService->createPost($postData);

        // Log the response to inspect the actual structure
        Log::info('Post creation response: ' . $postResponse->getContent());

        // Decode the response into an array
        $postData = json_decode($postResponse->getContent(), true);

        // Check the exact structure of the response and extract the post ID
        if (!isset($postData['message']['id'])) {
            return response()->json(['error' => 'Post creation failed. No post ID found.'], 500);
        }

        $postId = $postData['message']['id'];

        // Now handle the media file upload
        $uploadedMedia = [];
        if ($request->hasFile('media_files')) {
            foreach ($request->file('media_files') as $file) {
                // Determine the media type (image or video)
                $mediaType = $this->getMediaType($file);

                // Upload the file and store metadata using the post ID
                $media = $this->azureBlobStorageService->uploadFileWithMetadata($file, $postId, 'post', $mediaType);

                // Add the media details to the array
                $uploadedMedia[] = $media;
            }
        }

        // Return the post along with uploaded media
        return $postData;
    }




    /**
     * Determine the media type (image or video) based on the file mime type.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return string
     */
    private function getMediaType($file)
    {
        $mimeType = $file->getMimeType();

        // Check if the file is an image
        if (strpos($mimeType, 'image') !== false) {
            return 'image';
        }

        // Check if the file is a video
        if (strpos($mimeType, 'video') !== false) {
            return 'video';
        }

        // Default media type if not image or video (optional)
        return 'unknown';
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