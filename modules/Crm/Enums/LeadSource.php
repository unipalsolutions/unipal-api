<?php

namespace Modules\Crm\Enums;

enum LeadSource: string
{
    case Referral = 'referral';
    case Website = 'website';
    case LinkedIn = 'linkedin';
    case ColdCall = 'cold_call';
    case Event = 'event';
    case ExistingClient = 'existing_client';
    case Partner = 'partner';
    case Other = 'other';
}
