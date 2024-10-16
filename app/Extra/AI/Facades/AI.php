<?php

namespace App\Extra\AI\Facades;

use App\Extra\AI\AIManager;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin AIManager
 */
class AI extends Facade
{
    /**
     * The registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return AIManager::class;
    }
}
