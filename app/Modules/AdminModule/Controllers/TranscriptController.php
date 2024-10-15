<?php

namespace App\Modules\AdminModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Modules\AdminModule\Services\TranscriptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TranscriptController extends Controller
{
    private $transcriptService;

    function __construct()
    {
        //Init models
        $this->transcriptService = new TranscriptService();
    }

    public function updateTranscript($id, Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'american_gpa' => 'required|numeric|max:4',
            ]);

            if ($validator->fails()) {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $this->transcriptService->updateTranscript($id, $request->all());
            $transcript = $this->transcriptService->getTranscript($id);

            return CommonResponse::getResponse(
                200,
                'Successfully Transcript Updated',
                'Successfully Transcript Updated',
                $transcript
            );
        } catch (\Exception $e) {
            Log::error($e);
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went to wrong'
            );
        }
    }

    public function deleteTranscript($id)
    {
        try {

            $this->transcriptService->deleteTranscript($id);
            return CommonResponse::getResponse(
                200,
                'Successfully Transcript Deleted',
                'Successfully Transcript Deleted',
            );
        } catch (\Exception $e) {
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went to wrong'
            );
        }
    }

    public function getTranscripts(Request $request)
    {
        try {

            $transcripts = $this->transcriptService->getTranscripts($request->all());
            $transcripts = $transcripts->map(function ($transcript) {
                $media = $this->transcriptService->getTranscriptPath($transcript->id);
                if ($media) {
                    $transcript->path = $media['url'];
                }
                return $transcript;
            });
            return CommonResponse::getResponse(
                200,
                'Successfully Transcript Fetched',
                'Successfully Transcript Fetched',
                $transcripts
            );
        } catch (\Exception $e) {
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went to wrong'
            );
        }
    }
}
