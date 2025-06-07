<?php

namespace Hyrograsper\LunarFortis\Concerns;

enum CvvResponseCode: string
{
    case M = 'Match';
    case N = 'No Match';
    case P = 'Not Processed';
    case S = 'Unreadable';
    case U = 'Unknown, Issuer does not participate';
    case X = 'Service Provider did not respond';
}
