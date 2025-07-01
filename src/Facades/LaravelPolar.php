<?php

namespace ConfettiCode\LaravelPolar\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \ConfettiCode\LaravelPolar\LaravelPolar
 */
class LaravelPolar extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \ConfettiCode\LaravelPolar\LaravelPolar::class;
    }
}
