<?php

namespace App\Models\Enums;

enum Sentiments: string
{
    case POSITIVE = 'positive';
    case NEUTRAL = 'neutral';
    case NEGATIVE = 'negative';
    case NOT_DETERMINED = 'not_determined';

    public function label(): string
    {
        return match($this) {
            self::POSITIVE => 'Positive',
            self::NEUTRAL => 'Neutral',
            self::NEGATIVE => 'Negative',
            self::NOT_DETERMINED => 'Not Determined',
        };
    }

    public function severity(): string
    {
        return match($this) {
            self::POSITIVE => 'success',
            self::NEUTRAL => 'warn',
            self::NEGATIVE => 'danger',
            self::NOT_DETERMINED => 'secondary',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::POSITIVE => 'green',
            self::NEUTRAL => 'yellow',
            self::NEGATIVE => 'red',
            self::NOT_DETERMINED => 'gray',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::POSITIVE => '😊',
            self::NEUTRAL => '😐',
            self::NEGATIVE => '😞',
            self::NOT_DETERMINED => '❓',
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
