<?php

namespace Hyrograsper\LunarFortis\Facades;

use Hyrograsper\LunarFortis\LunarFortis as LunarFortisBase;
use Illuminate\Support\Facades\Facade;

/**
 * @see LunarFortisBase
 */
class LunarFortis extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LunarFortisBase::class;
    }
}
