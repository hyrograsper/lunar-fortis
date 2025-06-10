<?php

namespace Hyrograsper\LunarFortis\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Hyrograsper\LunarFortis\LunarFortis
 */
class LunarFortis extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Hyrograsper\LunarFortis\LunarFortis::class;
    }
}
