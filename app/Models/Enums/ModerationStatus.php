<?php

namespace App\Models\Enums;

enum ModerationStatus: string
{
    case PUBLISHED = 'published';
    case SOFT_FLAGGED = 'soft_flagged';
    case FLAGGED = 'flagged';
    case HIDDEN = 'hidden';

    public function label(): string
    {
        return match($this) {
            self::PUBLISHED => 'Published',
            self::SOFT_FLAGGED => 'Needs Review',
            self::FLAGGED => 'Flagged',
            self::HIDDEN => 'Hidden',
        };
    }

    public function severity(): string
    {
        return match($this) {
            self::PUBLISHED => 'success',
            self::SOFT_FLAGGED => 'info',
            self::FLAGGED => 'warn',
            self::HIDDEN => 'danger',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PUBLISHED => 'green',
            self::SOFT_FLAGGED => 'blue',
            self::FLAGGED => 'yellow',
            self::HIDDEN => 'red',
        };
    }

    public function serialize(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label(),
            'severity' => $this->severity(),
            'color' => $this->color(),
        ];
    }
}
