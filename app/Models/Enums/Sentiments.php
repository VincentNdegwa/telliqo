<?php

namespace App\Models\Enums;

enum Sentiments: string
{
    case POSITIVE = 'positive';
    case NEUTRAL = 'neutral';
    case NEGATIVE = 'negative';

    public function label(): string
    {
        return match($this) {
            self::POSITIVE => 'Positive',
            self::NEUTRAL => 'Neutral',
            self::NEGATIVE => 'Negative',
        };
    }

    public function severity(): string
    {
        return match($this) {
            self::POSITIVE => 'success',
            self::NEUTRAL => 'warn',
            self::NEGATIVE => 'danger',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::POSITIVE => 'green',
            self::NEUTRAL => 'yellow',
            self::NEGATIVE => 'red',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::POSITIVE => '😊',
            self::NEUTRAL => '😐',
            self::NEGATIVE => '😞',
        };
    }

    public function serialize(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label(),
            'severity' => $this->severity(),
            'color' => $this->color(),
            'icon' => $this->icon(),
        ];
    }
}
