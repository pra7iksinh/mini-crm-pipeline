<?php

namespace App\Enums;

enum LeadStatus: string
{
    case LEAD = 'lead';
    case CONTACTED = 'contacted';
    case PROPOSAL_SENT = 'proposal_sent';
    case WON = 'won';

    public function label(): string
    {
        return match ($this) {
            self::LEAD => 'Lead',
            self::CONTACTED => 'Contacted',
            self::PROPOSAL_SENT => 'Proposal Sent',
            self::WON => 'Won',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::LEAD => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300',
            self::CONTACTED => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
            self::PROPOSAL_SENT => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
            self::WON => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
        };
    }

    public function dotClass(): string
    {
        return match ($this) {
            self::LEAD => 'bg-zinc-400 dark:bg-zinc-500',
            self::CONTACTED => 'bg-blue-500',
            self::PROPOSAL_SENT => 'bg-amber-500',
            self::WON => 'bg-green-500',
        };
    }
}
