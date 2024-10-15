<?php

namespace App\Extra\Transcripts;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin TranscriptsManager
 */
class Transcripts extends Facade
{
    /**
     * Get facade accessor.
     */
    public static function getFacadeAccessor(): string
    {
        return TranscriptsManager::class;
    }
}
