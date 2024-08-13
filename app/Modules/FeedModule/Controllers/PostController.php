<?php

namespace App\Modules\FeedModule\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\FeedModule\Services\FeedService;
use App\Models\Feed;
use Illuminate\Http\Request;

class PostController extends Controller
{
    protected $feedService;

    public function __construct(FeedService $feedService)
    {
        $this->feedService = $feedService;
    }

    public function index(Feed $feed)
    {
        return $this->feedService->getAllPostsForFeed($feed);
    }

    public function show($id)
    {
        return $this->feedService->getPostById($id);
    }

    public function store(Request $request)
    {
        return $this->feedService->createFeedAndPost($request->all());
    }

    public function update(Request $request, $id)
    {
        return $this->feedService->updatePost($id, $request->all());
    }

    public function destroy($id)
    {
        return $this->feedService->deletePost($id);
    }
}
