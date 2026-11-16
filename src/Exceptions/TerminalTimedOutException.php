<?php

namespace Hyrograsper\LunarFortis\Exceptions;

use Exception;

class TerminalTimedOutException extends Exception
{
    public const string PROCESSOR_TIMEOUT = 'Time out waiting for processor.';
}
