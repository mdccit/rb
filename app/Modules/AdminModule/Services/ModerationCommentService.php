<?php


namespace App\Modules\AdminModule\Services;

use App\Models\ModerationComment;
use Carbon\Carbon;

class ModerationCommentService
{
    public function getAll ($morderation_id){
        
        $query = ModerationComment::connect(config('database.secondary'))
                ->join('users', 'users.id', '=' ,'moderation_comments.user_id')
                ->where('moderation_comments.moderation_request_id', $morderation_id)
                ->select(
                    'moderation_comments.id',
                    'moderation_comments.moderation_request_id',
                    'moderation_comments.comment',
                    'users.first_name',
                    'moderation_comments.created_at',
                    'moderation_comments.updated_at',
                )->get();
       
        return $query;
    }

    public function store (array $data){
        ModerationComment::connect(config('database.default'))
            ->create([
                'moderation_request_id'=> $data['morderation_id'],
                'user_id' => auth()->id(),
                'comment' => $data['comment']
        ]);
    }

}