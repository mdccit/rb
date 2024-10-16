<?php

namespace App\Extra\PDF;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin PDFManager
 */
class PDF extends Facade
{
    /**
     * Get the facade accessor.
     */
    protected static function getFacadeAccessor()
    {
        return PDFManager::class;
    }
}
