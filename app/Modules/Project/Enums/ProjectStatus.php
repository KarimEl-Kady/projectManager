<?php

namespace App\Modules\Project\Enums;

enum ProjectStatus: int
{
    case Active = 1;
    case Completed = 2;
    case Archived = 3;

    public function label(): string
    {
        return match ($this) {
            self::Active => 'active',
            self::Completed => 'completed',
            self::Archived => 'archived',
        };
    }

    public static function valueFromLabel(mixed $value): mixed
    {
        if (! is_string($value) || is_numeric($value)) {
            return $value;
        }

        foreach (self::cases() as $status) {
            if ($status->label() === str($value)->lower()->replace(' ', '_')->toString()) {
                return $status->value;
            }
        }

        return $value;
    }
}
