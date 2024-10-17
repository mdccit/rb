<?php

namespace App\Extra\PDF;

use Illuminate\Support\Str;
use Spatie\PdfToText\Exceptions\PdfNotFound;
use Spatie\PdfToText\Pdf as PdfToText;
use Spatie\PdfToImage\Pdf as PdfToImage;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use thiagoalessio\TesseractOCR\TesseractOCR;

class PDFManager
{
    /**
     * Convert a PDF file to text.
     *
     * @throws PdfNotFound
     */
    public function fileToText(string $file, string $ocrLanguage): PDFText
    {
        $directText = $this->pagesToText($file);

        if ($directText) {
            return new PDFText($directText, false);
        }

        $tmp = TemporaryDirectory::make(storage_path('transcript_temp'));

        $imageFiles = $this->fileToImages($file,$tmp);

        $ocrOutput = collect($imageFiles)->map(fn ($imageFile) => (
            (new TesseractOCR($imageFile))
                ->executable(config('transcripts.bin_files.tesseract'))
                ->psm(1)
                ->oem(3)
                ->lang($ocrLanguage)
                ->run()
        ))->filter()->join("\n");

        $tmp->delete();

        return new PDFText(
            Str::remove(config('transcripts.ocr_blacklist'), $ocrOutput),
            true
        );
    }

    /**
     * Convert a PDF to text.
     *
     * @throws PdfNotFound
     */
    public function pagesToText(string $file): string
    {
        return (new PdfToText(config('transcripts.bin_files.pdftotext')))
                ->setPdf($file)
                ->text();
    }

    /**
     * Convert a PDF to images.
     */
    public function fileToImages(string $file,$tmp): array
    {

        $pdf = (new PdfToImage($file))
            ->setOutputFormat('png');

        $paths = [];

        $pdfPagesCount = $pdf->getNumberOfPages();


        for ($i = 1; $i < $pdfPagesCount; $i++) {
            $path = $tmp->path("page-$i.png");
            $paths[] = $path;
            $pdf->setPage($i)
                ->saveImage($path);
        }

        return $paths;
    }
}
