<?php

namespace Hyrograsper\LunarFortis\PaymentTypes;

use ReflectionObject;

trait ResetsAllowPartialPayment
{
    private function resetAllowPartialPayment(): void
    {
        $reflection = new ReflectionObject($this);

        if (! $reflection->hasProperty('allowPartialPayment')) {
            return;
        }

        $reflection->getProperty('allowPartialPayment')->setValue($this, false);
    }
}
