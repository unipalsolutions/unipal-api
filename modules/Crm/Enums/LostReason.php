<?php

namespace Modules\Crm\Enums;

enum LostReason: string
{
    case Price = 'price';
    case NoResponse = 'no_response';
    case Competitor = 'competitor';
    case NotAFit = 'not_a_fit';
    case Timing = 'timing';
    case Cancelled = 'cancelled';
    case Other = 'other';
}
