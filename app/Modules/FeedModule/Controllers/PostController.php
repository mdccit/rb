<?php

namespace App\Modules\FeedModule\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\FeedModule\Services\FeedService;
use Illuminate\Http\Request;

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
        // Retrieve and return all posts, optionally filtered by type
        return $this->feedService->getAllPosts($request->query('type'));
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
     * Store a newly created post.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Create and return the newly created post
        return $this->feedService->createPost($request->all());
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
    public function addLike($postId, Request $request)
    {
        return $this->feedService->addLike($postId, $request->user_id);
    }

    /**
     * Remove a like from a post.
     *
     * @param int $postId
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeLike($postId, Request $request)
    {
        return $this->feedService->removeLike($postId, $request->user_id);
    }
}
