<?php


namespace App\Modules\UserModule\Services;

use App\Models\Player;
use App\Models\Transcript;
use App\Traits\AzureBlobStorage;

class TranscriptService
{
    use AzureBlobStorage;

    public function createTranscript(array $data)
    {
        $player = Player::connect(config('database.default'))
            ->where('user_id', auth()->id())
            ->first();
        Transcript::connect(config('database.default'))
            ->create([
                'user_id' => auth()->id(),
                'player_id' => $player->id,
                'country_id' => $data['country'],
                'language' => $data['language'],
                'file_name' => $data['file_name'],
                'status' => 'pending',
                'processed_at' => null,
                'local_gpa' => null,
                'american_gpa' => null,
            ]);
    }

    public function getTranscript()
    {
        return Transcript::connect(config('database.secondary'))
            ->where('user_id', auth()->id())
            ->first();
    }

    public function getPlayer()
    {
        return Player::connect(config('database.default'))
            ->where('user_id', auth()->id())
            ->first();
    }

    public function deleteTranscript($transcript_id){
        Transcript::connect(config('database.default'))->destroy($transcript_id);
        $player = $this->getPlayer();
        $player->gpa = 0;
        $player->save();
        $medias = $this->getMultipleFilesByEntityId($transcript_id, 'transcript');
        foreach ($medias as $media) {
            $this->removeFile($media['media_id']);
        }
    }

    public function uploadTranscript ($file, $transcriptId){
        return $this->uploadSingleFile($file, $transcriptId, 'transcript');
    }

    public function getTranscriptPath ($transcriptId){
        return $this->getSingleFileByEntityId( $transcriptId, 'transcript');
    }
}
