<?php

namespace App\Extra\Transcripts;

use App\Extra\AI\Facades\AI;
use App\Extra\AI\Prompts\TranscriptGpaCalculationPrompt;
use App\Extra\PDF\PDF;
use App\Extra\Transcripts\Exceptions\CouldNotCalculateGPAException;
use App\Extra\Transcripts\Exceptions\TranscriptException;
use Illuminate\Support\Facades\Log;

class TranscriptsManager
{
    /**
     * Calculate the GPA from a given transcript PDF file.
     *
     * @throws \Spatie\PdfToText\Exceptions\PdfNotFound
     * @throws CouldNotCalculateGPAException
     */
    public function calculateFromFile(string $file, string $originCountry, string $language, string $languageName): Transcript
    {
        $textPDF = PDF::fileToText($file, $language);

        $reply = AI::send(
            new TranscriptGpaCalculationPrompt(
                $textPDF->text,
                $textPDF->ocr,
                $originCountry,
                $languageName
            )
        );
        // $data = null;

        $data = $reply->response->json();

        if (isset($data['error'])) {
            throw new TranscriptException($data['error']);
        }

        if (! isset($data['local_gpa'])) {
            throw new CouldNotCalculateGPAException('local');
        }

        if ($originCountry === 'United States') {
            $data['american_gpa'] = $data['local_gpa'];
        }

        if (! isset($data['american_gpa'])) {
            throw new CouldNotCalculateGPAException('american');
        }

        return new Transcript(
            $data['local_gpa'],
            $data['american_gpa']
        );
    }
}
