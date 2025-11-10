<?php

namespace NckRtl\HttpManager\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \NckRtl\HttpManager\HttpManager
 */
class HttpManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \NckRtl\HttpManager\HttpManager::class;
    }
}
