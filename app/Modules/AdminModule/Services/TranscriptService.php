<?php

namespace App\Modules\AdminModule\Services;

use App\Models\Player;
use App\Models\Transcript;
use App\Traits\AzureBlobStorage;

class TranscriptService
{
    use AzureBlobStorage;

    public function updateTranscript($transcriptId, array $data)
    {
        $transcript = Transcript::connect(config('database.default'))
            ->where('id', $transcriptId)
            ->first();
        $transcript->american_gpa = $data['american_gpa'];
        $transcript->status = 'manual_approved';
        $transcript->save();
        $player = $this->getPlayer($transcript->player_id);
        $player->gpa = $data['american_gpa'];
        $player->save();
    }

    public function getTranscript($transcriptId)
    {
        return Transcript::connect(config('database.default'))
            ->where('id', $transcriptId)
            ->first();
    }

    public function getTranscripts(array $data)
    {
        $per_page_items = array_key_exists("per_page_items", $data) ? $data['per_page_items'] : 0;
        $status = array_key_exists("status", $data) ? $data['status'] : null;
        $search_key = array_key_exists("search_key", $data) ? $data['search_key'] : null;

        $query = Transcript::connect(config('database.secondary'))
            ->join('users', 'users.id', '=', 'transcripts.user_id')
            ->select(
                'transcripts.id',
                'users.display_name',
                'users.email',
                'file_name',
                'status',
                'processed_at',
                'american_gpa',
            );

        if ($status != null) {
            $query->where('status', $status);
        }

        if ($search_key != null) {
            $query->where(function ($q) use ($search_key) {
                $q->where('users.display_name', 'LIKE', '%' . $search_key . '%')
                    ->orWhere('users.email', 'LIKE', '%' . $search_key . '%');
            });
        }

        $dataSet = array();

        if ($per_page_items != 0) {
            $dataSet = $query->paginate($per_page_items);
        } else {
            $dataSet = $query->get();
        }

        return $dataSet;
    }

    public function getPlayer($playerId)
    {
        return Player::connect(config('database.default'))
            ->where('id', $playerId)
            ->first();
    }

    public function deleteTranscript($transcript_id){
        $transcript = $this->getTranscript($transcript_id);
        $player = $this->getPlayer($transcript->player_id);
        $player->gpa = 0;
        $player->save();
        Transcript::connect(config('database.default'))->destroy($transcript_id);
        $medias = $this->getMultipleFilesByEntityId($transcript_id, 'transcript');
        foreach ($medias as $media) {
            $this->removeFile($media['media_id']);
        }
    }

    public function getTranscriptPath($transcriptId)
    {
        return $this->getSingleFileByEntityId($transcriptId, 'transcript');
    }
}
