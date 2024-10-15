<?php

namespace App\Jobs;

use App\Extra\Transcripts\Exceptions\TranscriptException;
use App\Extra\Transcripts\Transcripts;
use App\Models\Player;
use App\Models\Transcript;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class ProcessTranscript implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Constructor.
     */
    public function __construct(
        public readonly Player $player,
        public readonly Transcript $transcript,
        public readonly string $mediaPath,
        public readonly string $countryName,
        public readonly string $language,
        public readonly string $languageName,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $calculatedGPA = Transcripts::calculateFromFile(
                $this->mediaPath,
                $this->countryName,
                $this->transcript->language,
                $this->languageName
            );
            $this->transcript->update([
                'processed_at' => now(),
                'american_gpa' => $calculatedGPA->americanGPA,
                'local_gpa' => $calculatedGPA->localGPA,
                'status' => "ai_approved",
            ]);

            $this->player->update([
                'gpa' => $calculatedGPA->americanGPA
            ]);

        } catch(TranscriptException $exception) {
            $this->transcript->update([
                'processed_at' => now(),
                'error' => $exception->getMessage() ?? 'Unknown error',
                'status' => "failed",
            ]);
        }
    }
}
