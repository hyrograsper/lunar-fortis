<?php

namespace Hyrograsper\LunarFortis\Enums;

enum AvsResponseCode: string
{
    case GOOD = 'Street or zip are both good (if provided)';
    case BAD = 'Both street and zip do not match';
    case STREET = 'Street does not match';
    case ZIP = 'Zip does not match';
}
