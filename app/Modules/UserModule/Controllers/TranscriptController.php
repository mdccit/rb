<?php

namespace App\Modules\UserModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessTranscript;
use App\Models\Country;
use App\Modules\UserModule\Services\TranscriptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Spatie\PdfToImage\Pdf as PdfToImage;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class TranscriptController extends Controller
{
    private $transcriptService;

    function __construct()
    {
        //Init models
        $this->transcriptService = new TranscriptService();
    }

    public function createTranscript(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'file' => 'required|mimes:pdf|max:51200',
                'country' => 'required|numeric',
                'language' => 'required|string',
                'file_name' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $this->transcriptService->createTranscript($request->all());
            $transcript = $this->transcriptService->getTranscript();
            $media = $this->transcriptService->uploadTranscript($request->file('file'), $transcript->id);

            $filename = 'transcript.pdf';
            $transcriptPath = tempnam(TemporaryDirectory::make()->path(), $filename);
            copy($media['url'], $transcriptPath);

            $country = Country::connect(config('database.secondary'))
                ->where('id', $transcript->country_id)
                ->first();


            $languageName = config('ocr.languages')[$transcript->language];
            $player = $this->transcriptService->getPlayer();
            ProcessTranscript::dispatchSync($player, $transcript, $transcriptPath, $country->name, $transcript->language, $languageName);

            $tmp = TemporaryDirectory::make();
            $pdf = (new PdfToImage($transcriptPath))
                ->setOutputFormat('png');
            $path = $tmp->path("page-1.png");
            $pdf->setPage(1)
                ->saveImage($path);
            $image = base64_encode(file_get_contents($path));
            $transcript->preview = $image;

            return CommonResponse::getResponse(
                200,
                'Successfully Transcript Created',
                'Successfully Transcript Created',
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

    public function getTranscript()
    {
        try {
            $transcript = $this->transcriptService->getTranscript();
            if (!$transcript) {
                return CommonResponse::getResponse(
                    404,
                    'Transcript not found',
                    'Transcript not found'
                );
            }
            $media = $this->transcriptService->getTranscriptPath($transcript->id);
            $filename = 'transcript.pdf';
            $transcriptPath = tempnam(TemporaryDirectory::make()->path(), $filename);
            copy($media['url'], $transcriptPath);
            $tmp = TemporaryDirectory::make();
            $pdf = (new PdfToImage($transcriptPath))
                ->setOutputFormat('png');
            $path = $tmp->path("page-1.png");
            $pdf->setPage(1)
                ->saveImage($path);
            $image = base64_encode(file_get_contents($path));
            $transcript->preview = $image;
            $transcript->path = $media['url'];
            $player = $this->transcriptService->getPlayer();
            $transcript->gpa = $player->gpa;

            return CommonResponse::getResponse(
                200,
                'Successfully Transcript Fetched',
                'Successfully Transcript Fetched',
                $transcript
            );
        } catch (\Exception $e) {
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
}
