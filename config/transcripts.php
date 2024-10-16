<?php

return [
    'bin_files' => [
        'pdftotext' => env('PDFTOTEXT_BIN', '/usr/bin/pdftotext'),
        'tesseract' => env('TESSERACT_BIN', '/usr/bin/tesseract'),
    ],

    'ocr_blacklist' => [
        '[',
        ']',
    ]
];
