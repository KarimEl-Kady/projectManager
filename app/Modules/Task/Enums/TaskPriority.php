<?php

namespace App\Modules\Task\Enums;

enum TaskPriority: int
{
    case Low = 1;
    case Medium = 2;
    case High = 3;

    public function label(): string
    {
        return match ($this) {
            self::Low => 'low',
            self::Medium => 'medium',
            self::High => 'high',
        };
    }

    public static function valueFromLabel(mixed $value): mixed
    {
        if (! is_string($value) || is_numeric($value)) {
            return $value;
        }

        foreach (self::cases() as $priority) {
            if ($priority->label() === str($value)->lower()->replace(' ', '_')->toString()) {
                return $priority->value;
            }
        }

        return $value;
    }
}
