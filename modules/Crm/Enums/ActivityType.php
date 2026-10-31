<?php

namespace Modules\Crm\Enums;

enum ActivityType: string
{
    case Call = 'call';
    case Email = 'email';
    case Meeting = 'meeting';
    case Proposal = 'proposal';
    case FollowUp = 'follow_up';
    case Note = 'note';
}
