<?php

namespace Modules\Crm\Enums;

/** Pipeline stages and default win probabilities, from the BD pipeline sheet (Config tab). */
enum LeadStage: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case MeetingScheduled = 'meeting_scheduled';
    case MeetingHeld = 'meeting_held';
    case ProposalSent = 'proposal_sent';
    case Negotiation = 'negotiation';
    case VerbalCommit = 'verbal_commit';
    case Won = 'won';
    case Lost = 'lost';
    case Paused = 'paused';

    public function defaultProbability(): int
    {
        return match ($this) {
            self::New => 10,
            self::Contacted => 20,
            self::MeetingScheduled => 30,
            self::MeetingHeld => 40,
            self::ProposalSent => 50,
            self::Negotiation => 65,
            self::VerbalCommit => 85,
            self::Won => 100,
            self::Lost, self::Paused => 0,
        };
    }

    public function isClosed(): bool
    {
        return in_array($this, [self::Won, self::Lost], true);
    }
}
