<?php

namespace App\Models\Enums;

enum ModerationStatus: string
{
    case PUBLISHED = 'published';
    case FLAGGED = 'flagged';
    case HIDDEN = 'hidden';

    public function label(): string
    {
        return match($this) {
            self::PUBLISHED => 'Published',
            self::FLAGGED => 'Flagged',
            self::HIDDEN => 'Hidden',
        };
    }

    public function severity(): string
    {
        return match($this) {
            self::PUBLISHED => 'success',
            self::FLAGGED => 'warn',
            self::HIDDEN => 'danger',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PUBLISHED => 'green',
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
